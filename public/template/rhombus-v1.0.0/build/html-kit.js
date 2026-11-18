/**
 * html-kit — a tiny Vite plugin that renders every HTML page through Handlebars.
 *
 * - Partials:  src/partials/ (*.hbs)  → {{> sidebar}}, {{> icons/bell}}, {{#> layout}}...{{/layout}}
 * - Data:      src/data/site.json        → available as {{@root.site}}
 * - {{root}}:  relative path from the current page to the project root (for links)
 */
import fs from 'node:fs';
import path from 'node:path';
import Handlebars from 'handlebars';

export default function htmlKit() {
  let root = process.cwd();

  const partialsDir = () => path.join(root, 'src/partials');
  const dataFile = () => path.join(root, 'src/data/site.json');

  function createEngine() {
    const hb = Handlebars.create();

    // nested partials are registered by path, e.g. {{> icons/bell}}
    for (const file of fs.readdirSync(partialsDir(), { recursive: true })) {
      if (file.endsWith('.hbs')) {
        const name = file.slice(0, -'.hbs'.length).split(path.sep).join('/');
        hb.registerPartial(name, fs.readFileSync(path.join(partialsDir(), file), 'utf8'));
      }
    }

    hb.registerHelper('eq', (a, b) => a === b);
    hb.registerHelper('gte', (a, b) => a >= b);
    hb.registerHelper('add', (a, b) => a + b);
    // {{> (concat 'icons/' icon)}} renders an icon by name
    hb.registerHelper('concat', (...args) => args.slice(0, -1).join(''));
    // {{year}} for the footer copyright
    hb.registerHelper('year', () => new Date().getFullYear());

    return { hb, site: JSON.parse(fs.readFileSync(dataFile(), 'utf8')) };
  }

  return {
    name: 'html-kit',

    configResolved(config) {
      root = config.root;
    },

    configureServer(server) {
      server.watcher.add([partialsDir(), dataFile()]);
      server.watcher.on('change', (file) => {
        if (file.startsWith(partialsDir()) || file === dataFile()) server.ws.send({ type: 'full-reload' });
      });
    },

    transformIndexHtml: {
      order: 'pre',
      handler(html, ctx) {
        const { hb, site } = createEngine();
        const relDir = path.dirname(path.relative(root, ctx.filename));
        const rootPath =
          relDir === '.'
            ? ''
            : relDir
                .split(path.sep)
                .map(() => '..')
                .join('/') + '/';
        return hb.compile(html, { preventIndent: true })({ site, root: rootPath });
      },
    },
  };
}
