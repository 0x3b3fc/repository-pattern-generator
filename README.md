# Repository Pattern Generator

This package provides a convenient way to generate repository patterns for your Laravel application. It helps to
streamline the code structure and enhance maintainability by encapsulating data access logic.

## Features

- Automatically generates repository interfaces and implementations
- Supports dependency injection for easy testing
- Customizable stub files for fine-tuning the generated code

## Installation

Install the package via Composer:

```sh
composer require phpsamurai/repository-pattern-generator
```

## Usage

### Register the Service Provider

If you're using Laravel 5.5+, the package will auto-register the service provider. For earlier versions of Laravel, add
the service provider in `config/app.php`:

```php
'providers' => [
    // Other service providers...
    phpsamurai\RepositoryPatternGenerator\RepositoryPatternGeneratorServiceProvider::class,
],
```

### Publish the Configuration

You can publish the configuration file with the following command:

```sh
php artisan vendor:publish
```

Then 
```
RepositoryPatternGeneratorServiceProvider
```

### Generate a Repository

To generate a new repository, use the Artisan command:

```sh
php artisan make:repository {ModelName}
```

For instance, to create a repository for a `User` model, you would execute:

```sh
php artisan make:repository User
```

### Custom Stubs

You can customize the stub files used to generate the repository and interface. To do this, publish the stub files:

```sh
php artisan vendor:publish --provider="phpsamurai\RepositoryPatternGenerator\RepositoryPatternGeneratorServiceProvider" --tag="stubs"
```

Edit the stub files located in the `stubs/repository` directory to tailor them to your needs. The generator will use
these modified stubs when creating new files.

## Testing

To ensure the generated repository pattern integrates well with your Laravel application, you can write and run your
tests using PHPUnit. Mocking dependencies is straightforward with the repository pattern, facilitating a cleaner and
more maintainable test suite.

## Contributing

Contributions are welcome! Please follow these steps to contribute:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature-name`).
3. Commit your changes (`git commit -m 'Add some feature'`).
4. Push to the branch (`git push origin feature/your-feature-name`).
5. Open a pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE.md) file for details.
