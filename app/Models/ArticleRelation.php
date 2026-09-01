<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleRelation extends Model
{
    protected $fillable = ['article_id', 'relatable_type', 'relatable_id'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function relatable()
    {
        return $this->morphTo();
    }
}
