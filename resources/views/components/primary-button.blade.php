<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-purple-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-wider hover:bg-purple-800 focus:bg-purple-800 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer shadow-xs']) }}>
    {{ $slot }}
</button>
