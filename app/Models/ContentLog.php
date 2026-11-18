<?php

namespace App\Models;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Enums\EvidenceStatus;
use Database\Factories\ContentLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentLog extends Model
{
    /** @use HasFactory<ContentLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_date',
        'platform',
        'content_type',
        'title',
        'pillar',
        'pic_name',
        'evidence_url',
        'evidence_status',
        'h30_due_date',
        'data_status',
        'views_h30',
        'viewers_h30',
        'likes',
        'comments',
        'shares_reposts',
        'saves',
        'follows',
        'total_interactions',
        'engagement_rate',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'post_date' => 'date',
            'h30_due_date' => 'date',
            'content_type' => ContentType::class,
            'pillar' => ContentPillar::class,
            'data_status' => DataStatus::class,
            'evidence_status' => EvidenceStatus::class,
            'views_h30' => 'integer',
            'viewers_h30' => 'integer',
            'likes' => 'integer',
            'comments' => 'integer',
            'shares_reposts' => 'integer',
            'saves' => 'integer',
            'follows' => 'integer',
            'total_interactions' => 'integer',
            'engagement_rate' => 'decimal:4',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::saving(function (self $log) {
            // Otomatisasi tanggal jatuh tempo H+30 jika belum ditentukan
            if ($log->post_date && blank($log->h30_due_date)) {
                $log->h30_due_date = $log->post_date->copy()->addDays(30);
            }

            // Otomatisasi status evidence berdasarkan URL
            if (! empty($log->evidence_url)) {
                $log->evidence_status = EvidenceStatus::Ada;
            } elseif (blank($log->evidence_status)) {
                $log->evidence_status = EvidenceStatus::Belum;
            }

            // Otomatisasi total interaksi
            $log->total_interactions = (int) $log->likes
                + (int) $log->comments
                + (int) $log->shares_reposts
                + (int) $log->saves;

            // Otomatisasi kalkulasi engagement rate (%)
            if ((int) $log->views_h30 > 0) {
                $log->engagement_rate = round(($log->total_interactions / $log->views_h30) * 100, 4);
            } else {
                $log->engagement_rate = 0.0000;
            }

            // Otomatisasi status data jika belum diset
            if (blank($log->data_status)) {
                if ((int) $log->views_h30 > 0 || $log->total_interactions > 0) {
                    $log->data_status = DataStatus::Completed;
                } else {
                    $log->data_status = DataStatus::PendingH30;
                }
            }
        });
    }

    /**
     * Scope untuk konten yang sudah dievaluasi / diisi metriknya.
     *
     * @param  Builder<self>  $query
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('data_status', DataStatus::Completed);
    }

    /**
     * Scope untuk konten yang masih menunggu evaluasi H+30.
     *
     * @param  Builder<self>  $query
     */
    public function scopePendingH30(Builder $query): void
    {
        $query->where('data_status', DataStatus::PendingH30);
    }

    /**
     * Scope untuk konten yang telah melewati jatuh tempo H+30 namun belum diisi.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOverdueH30(Builder $query): void
    {
        $query->where('data_status', DataStatus::PendingH30)
            ->whereDate('h30_due_date', '<', now());
    }

    /**
     * Scope filter berdasarkan tahun publikasi.
     *
     * @param  Builder<self>  $query
     */
    public function scopeByYear(Builder $query, int $year): void
    {
        $query->whereYear('post_date', $year);
    }

    /**
     * Scope filter berdasarkan bulan (dan tahun opsional).
     *
     * @param  Builder<self>  $query
     */
    public function scopeByMonth(Builder $query, int $month, ?int $year = null): void
    {
        $query->whereMonth('post_date', $month);

        if ($year !== null) {
            $query->whereYear('post_date', $year);
        }
    }

    /**
     * Scope filter berdasarkan pilar konten.
     *
     * @param  Builder<self>  $query
     */
    public function scopeByPillar(Builder $query, ContentPillar|string $pillar): void
    {
        $value = $pillar instanceof ContentPillar ? $pillar->value : $pillar;
        $query->where('pillar', $value);
    }

    /**
     * Scope filter berdasarkan format / jenis konten.
     *
     * @param  Builder<self>  $query
     */
    public function scopeByContentType(Builder $query, ContentType|string $contentType): void
    {
        $value = $contentType instanceof ContentType ? $contentType->value : $contentType;
        $query->where('content_type', $value);
    }
}
