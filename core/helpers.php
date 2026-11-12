<?php
/** Escape output to prevent XSS. Use in every view. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
