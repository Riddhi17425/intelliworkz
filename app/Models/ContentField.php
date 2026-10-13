<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentField extends Model
{
    public const TYPES = ['text' => 'Text', 'textarea' => 'Paragraph', 'html' => 'Formatted text', 'url' => 'Link', 'image' => 'Image', 'toggle' => 'Show / hide'];

    protected $fillable = ['value'];
}
