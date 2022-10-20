<?php

class Component
{
    public static function render(string $view, array $params = []): string
    {
        if (!file_exists($file = dirname(__FILE__) . '/' . $view . '/' . $view . '.php')) {
            return sprintf('The component "%s" could not be found.', $view);
        }

        ob_start();
        include($file);

        return ob_get_clean();
    }
}
