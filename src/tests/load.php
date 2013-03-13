<?php

call_user_func(
    function () {
        $loader = require __DIR__ . '/../vendors/autoload.php';

        $loader->add(null, __DIR__);

        org\bovigo\vfs\vfsStreamWrapper::register();
    }
);

