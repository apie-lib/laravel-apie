This is a the default dashboard template.

You can override this in config/apie.php.

<blockquote><pre>// apie.php
return [
    'cms' => [
        'dashboard_template' => 'apie/dashboard'
    ]
];</pre></blockquote>

Now create a template in your project in templates/apie/dashboard.blade.php without the HTML layout and you have
your own custom dashboard!