<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit46b346d619701d475195320b6fd789eb
{
    public static $files = array (
        '33aa49fdef06321ee6af35157a3be40a' => __DIR__ . '/../..' . '/includes/functions.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'FBPM_Admin_Module' => __DIR__ . '/../..' . '/includes/interfaces/interface-fbpm-admin-module.php',
        'FBPM_Auth' => __DIR__ . '/../..' . '/includes/admin/class-fbpm-auth.php',
        'FBPM_Container' => __DIR__ . '/../..' . '/includes/class-fbpm-container.php',
        'FBPM_Module' => __DIR__ . '/../..' . '/includes/interfaces/interface-fbpm-module.php',
        'FBPM_Options_Page' => __DIR__ . '/../..' . '/includes/admin/class-fbpm-options-page.php',
        'FBPM_Settings' => __DIR__ . '/../..' . '/includes/admin/class-fbpm-settings.php',
        'FBPM_Webhook' => __DIR__ . '/../..' . '/includes/admin/class-fbpm-webhook.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit46b346d619701d475195320b6fd789eb::$classMap;

        }, null, ClassLoader::class);
    }
}
