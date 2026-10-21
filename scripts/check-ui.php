<?php
// Validate compiled PHP and render shared components without creating business data.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../resources/views'));
$count = 0;
foreach ($views as $view) {
    if (!$view->isFile() || !str_ends_with($view->getFilename(), '.blade.php')) continue;
    if (isset($argv[1]) && !str_contains($view->getPathname(), $argv[1])) continue;
    $compiled = $app['blade.compiler']->compileString(file_get_contents($view->getPathname()));
    try {
        token_get_all($compiled, TOKEN_PARSE);
    } catch (ParseError $error) {
        file_put_contents(__DIR__.'/../storage/framework/ui-check-failure.php', $compiled);
        echo $view->getPathname().PHP_EOL.$error->getMessage().' on line '.$error->getLine().PHP_EOL;
        exit(1);
    }
    $count++;
}
echo "Compiled and syntax-checked {$count} Blade templates.".PHP_EOL;
if (!isset($argv[1])) {
    $app['view']->share('errors', new Illuminate\Support\ViewErrorBag);
    foreach (['auth.login', 'auth.register', 'wellness.create'] as $name) {
        $html = $app['view']->make($name)->render();
        if (str_contains($html, '<x-ui.') || !str_contains($html, 'ui-button--primary')) {
            throw new RuntimeException("Unresolved shared components in {$name}");
        }
        echo "Rendered {$name} with shared components.".PHP_EOL;
    }
}
