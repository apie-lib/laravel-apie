<h1>An error occurred. Please try again later.<h1>
@if (App::hasDebugModeEnabled())
{!! \Apie\LaravelApie\ErrorHandler\ApieErrorRenderer::renderException($error) !!}
@endif