<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitff69a621beb925ab247ba89a2b55463b
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'ScssPhp\\ScssPhp\\' => 16,
            'ScssLibrary\\' => 12,
        ),
        'B' => 
        array (
            'Baxtian\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ScssPhp\\ScssPhp\\' => 
        array (
            0 => __DIR__ . '/..' . '/scssphp/scssphp/src',
        ),
        'ScssLibrary\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Baxtian\\' => 
        array (
            0 => __DIR__ . '/..' . '/baxtian/php-singleton/src',
        ),
    );

    public static $classMap = array (
        'Baxtian\\Singleton' => __DIR__ . '/..' . '/baxtian/php-singleton/src/Singleton.php',
        'ScssLibrary\\ScssLibrary' => __DIR__ . '/../..' . '/src/ScssLibrary.php',
        'ScssLibrary\\Settings\\ScssLibrary' => __DIR__ . '/../..' . '/src/Settings/ScssLibrary.php',
        'ScssPhp\\ScssPhp\\Base\\Range' => __DIR__ . '/..' . '/scssphp/scssphp/src/Base/Range.php',
        'ScssPhp\\ScssPhp\\Block' => __DIR__ . '/..' . '/scssphp/scssphp/src/Block.php',
        'ScssPhp\\ScssPhp\\Cache' => __DIR__ . '/..' . '/scssphp/scssphp/src/Cache.php',
        'ScssPhp\\ScssPhp\\Colors' => __DIR__ . '/..' . '/scssphp/scssphp/src/Colors.php',
        'ScssPhp\\ScssPhp\\Compiler' => __DIR__ . '/..' . '/scssphp/scssphp/src/Compiler.php',
        'ScssPhp\\ScssPhp\\Compiler\\Environment' => __DIR__ . '/..' . '/scssphp/scssphp/src/Compiler/Environment.php',
        'ScssPhp\\ScssPhp\\Exception\\CompilerException' => __DIR__ . '/..' . '/scssphp/scssphp/src/Exception/CompilerException.php',
        'ScssPhp\\ScssPhp\\Exception\\ParserException' => __DIR__ . '/..' . '/scssphp/scssphp/src/Exception/ParserException.php',
        'ScssPhp\\ScssPhp\\Exception\\RangeException' => __DIR__ . '/..' . '/scssphp/scssphp/src/Exception/RangeException.php',
        'ScssPhp\\ScssPhp\\Exception\\ServerException' => __DIR__ . '/..' . '/scssphp/scssphp/src/Exception/ServerException.php',
        'ScssPhp\\ScssPhp\\Formatter' => __DIR__ . '/..' . '/scssphp/scssphp/src/Formatter.php',
        'ScssPhp\\ScssPhp\\Formatter\\Compact' => __DIR__ . '/..' . '/scssphp/scssphp/src/Formatter/Compact.php',
        'ScssPhp\\ScssPhp\\Formatter\\Compressed' => __DIR__ . '/..' . '/scssphp/scssphp/src/Formatter/Compressed.php',
        'ScssPhp\\ScssPhp\\Formatter\\Crunched' => __DIR__ . '/..' . '/scssphp/scssphp/src/Formatter/Crunched.php',
        'ScssPhp\\ScssPhp\\Formatter\\Debug' => __DIR__ . '/..' . '/scssphp/scssphp/src/Formatter/Debug.php',
        'ScssPhp\\ScssPhp\\Formatter\\Expanded' => __DIR__ . '/..' . '/scssphp/scssphp/src/Formatter/Expanded.php',
        'ScssPhp\\ScssPhp\\Formatter\\Nested' => __DIR__ . '/..' . '/scssphp/scssphp/src/Formatter/Nested.php',
        'ScssPhp\\ScssPhp\\Formatter\\OutputBlock' => __DIR__ . '/..' . '/scssphp/scssphp/src/Formatter/OutputBlock.php',
        'ScssPhp\\ScssPhp\\Node' => __DIR__ . '/..' . '/scssphp/scssphp/src/Node.php',
        'ScssPhp\\ScssPhp\\Node\\Number' => __DIR__ . '/..' . '/scssphp/scssphp/src/Node/Number.php',
        'ScssPhp\\ScssPhp\\Parser' => __DIR__ . '/..' . '/scssphp/scssphp/src/Parser.php',
        'ScssPhp\\ScssPhp\\SourceMap\\Base64' => __DIR__ . '/..' . '/scssphp/scssphp/src/SourceMap/Base64.php',
        'ScssPhp\\ScssPhp\\SourceMap\\Base64VLQ' => __DIR__ . '/..' . '/scssphp/scssphp/src/SourceMap/Base64VLQ.php',
        'ScssPhp\\ScssPhp\\SourceMap\\SourceMapGenerator' => __DIR__ . '/..' . '/scssphp/scssphp/src/SourceMap/SourceMapGenerator.php',
        'ScssPhp\\ScssPhp\\Type' => __DIR__ . '/..' . '/scssphp/scssphp/src/Type.php',
        'ScssPhp\\ScssPhp\\Util' => __DIR__ . '/..' . '/scssphp/scssphp/src/Util.php',
        'ScssPhp\\ScssPhp\\Version' => __DIR__ . '/..' . '/scssphp/scssphp/src/Version.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitff69a621beb925ab247ba89a2b55463b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitff69a621beb925ab247ba89a2b55463b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitff69a621beb925ab247ba89a2b55463b::$classMap;

        }, null, ClassLoader::class);
    }
}
