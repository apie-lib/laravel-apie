<h1>An error occurred. Please try again later.<h1>
@env('dev')
{{ \Apie\LaravelApie\ErrorHandler::renderException($error) }}
@endenv