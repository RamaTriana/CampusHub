<?php

function required($value)
{

    return trim($value) !== '';

}

function minLength($value, $min)
{

    return strlen(trim($value)) >= $min;

}

function email($email)
{

    return filter_var($email, FILTER_VALIDATE_EMAIL);

}

function imageExtension($file)
{

    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    return in_array(

        $extension,

        ALLOWED_IMAGE

    );

}