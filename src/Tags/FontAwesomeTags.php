<?php

namespace Sitestein\FontAwesome\Tags;

use Statamic\Tags\Tags;
use Facades\Sitestein\FontAwesome\FontAwesome;

class FontAwesomeTags extends Tags
{
    protected static $handle = 'font_awesome';

    public function wildcard($icon): string
    {
        return $this->output($this->context->get($icon));
    }

    public function kit(): string
    {
        $kitUrl = FontAwesome::kit($this->params->get('token'))->get('url');

        return "<script src='$kitUrl' crossorigin='anonymous'></script>";
    }

    protected function output($icon): string
    {
        $class = trim("{$icon->value()} {$this->params->get('class')}");

        return "<i class='$class' aria-hidden='true'></i>";
    }
}
