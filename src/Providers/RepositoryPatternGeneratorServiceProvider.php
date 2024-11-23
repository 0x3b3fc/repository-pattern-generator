<?php

namespace phpsamurai\RepositoryPatternGenerator\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryPatternGeneratorServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__ . '/../../config/repository_pattern_generator.php' => config_path('repository_pattern_generator.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../../config/repository_pattern_generator.php', 'repository_pattern_generator');
        $this->commands([
            \phpsamurai\RepositoryPatternGenerator\Console\RepositoryPatternMakeCommand::class,
        ]);
    }
}
