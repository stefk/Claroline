<?php

/**
 * This script creates a parameters.yml file (if it doesn't exist) from
 * values provided by the user or available as environment variables.
 *
 * The list of parameters is stored in $params, where each parameter has the
 * following attributes:
 *
 *   - name of the parameter in the parameters.yml(.dist) file
 *   - name of the corresponding environement variable, if any
 *   - default/dist value of the parameter
 */

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$paramFile = __DIR__ . '/app/config/parameters.yml';
$params = [
    ['database_host', 'DB_HOST', 'localhost'],
    ['database_name', 'DB_NAME', 'claroline'],
    ['database_user', 'DB_USER', 'root'],
    ['database_password', 'DB_PASSWORD', null],
    ['secret', 'SECRET', 'change_me']
];

if (file_exists($paramFile)) {
    echo "Nothing to configure, parameter file already exists\n";
    exit(0);
} else {
    echo "Please provide a value for the following parameters:\n";
}

$fileContent = file_get_contents("{$paramFile}.dist");

foreach ($params as $paramData) {
    $value = getParameter($paramData);
    $value = empty($value) ? '~' : $value;
    $pattern = "/( +{$paramData[0]} *: *)([^ ]+) *\\n/";
    $replace = "\${1}{$value}\n";
    $newContent = preg_replace($pattern, $replace, $fileContent, 1);

    if ($value !== $paramData[2] && $fileContent === $newContent) {
        throw new \Exception("Cannot set param {$paramData[0]}");
    }

    $fileContent = $newContent;
}

file_put_contents($paramFile, $fileContent);

echo "Config file app/config/parameters.yml written\n";
exit(0);

function getParameter(array $paramData)
{
    if ($value = getenv($paramData[1])) {
        echo "{$paramData[0]} -> provided by environment\n";

        return $value;
    }

    $defaultText = $paramData[2] ? " ({$paramData[2]})" : '';
    echo("{$paramData[0]}{$defaultText}: ");
    $input = stream_get_line(STDIN, 1024, PHP_EOL);

    return $input ?: $paramData[2];
}

