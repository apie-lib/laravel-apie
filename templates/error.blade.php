<h1>An error occurred. Please try again later.</h1>
@if (App::hasDebugModeEnabled())
<div>{!! \Apie\LaravelApie\ErrorHandler\ApieErrorRenderer::renderException($error) !!}</div>
@endif