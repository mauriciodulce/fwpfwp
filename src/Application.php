<?php



declare(strict_types=1);

namespace MauricioDulce;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\HttpFoundation\Request;

class Application
{
    /** @var string */
    protected $basePath;

    /** @var string|null */
    protected $publicPath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;

        try {
            $dotenv = Dotenv::createImmutable($this->basePath);
            $dotenv->load();
        } catch (InvalidPathException $exception) {
            //
        }
    }

    public function run(): void
    {
        // Set the environment type.
        // define('WP_ENVIRONMENT_TYPE', env('WP_ENV', 'production'));

        // For developers: WordPress debugging mode.
        $debug = env('WP_DEBUG', false);
        // define('WP_DEBUG', $debug);
        define('WP_DEBUG_LOG', env('WP_DEBUG_LOG', false));
        define('WP_DEBUG_DISPLAY', env('WP_DEBUG_DISPLAY', $debug));
        define('SCRIPT_DEBUG', env('SCRIPT_DEBUG', $debug));

        // The database configuration with database name, username, password,
        // hostname charset and database collae type.
        // define('DB_NAME', env('DB_NAME'));
        // define('DB_USER', env('DB_USER'));
        // define('DB_PASSWORD', env('DB_PASSWORD'));
        // define('DB_HOST', env('DB_HOST', '127.0.0.1'));
        // define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));
        // define('DB_COLLATE', env('DB_COLLATE', 'utf8mb4_unicode_ci'));

        // Detect HTTPS behind a reverse proxy or a load balancer.
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        // Set the unique authentication keys and salts.
        // define('AUTH_KEY', env('AUTH_KEY'));
        // define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
        // define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
        // define('NONCE_KEY', env('NONCE_KEY'));
        // define('AUTH_SALT', env('AUTH_SALT'));
        // define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
        // define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
        // define('NONCE_SALT', env('NONCE_SALT'));

        // Set the home url to the current domain.
        $request = Request::createFromGlobals();
        define('WP_HOME', env('WP_URL', $request->getSchemeAndHttpHost()));

        // Set the WordPress directory path.
        define('WP_SITEURL', env('WP_SITEURL', sprintf('%s/%s', WP_HOME, env('WP_DIR', 'wordpress'))));

        // Set the WordPress content directory path.
        define('WP_CONTENT_DIR', env('WP_CONTENT_DIR', $this->getPublicPath()));
        define('WP_CONTENT_URL', env('WP_CONTENT_URL', WP_HOME));

        // Set the trash to less days to optimize WordPress.
        define('EMPTY_TRASH_DAYS', env('EMPTY_TRASH_DAYS', 7));

        // Set the default WordPress theme.
        define('WP_DEFAULT_THEME', env('WP_THEME', 'theme_'));

        define('DISABLE_WP_CRON', 'true');

        // Constant to configure core updates.
        define('WP_AUTO_UPDATE_CORE', env('WP_AUTO_UPDATE_CORE', 'minor'));

        // Specify the number of post revisions.
        define('WP_POST_REVISIONS', env('WP_POST_REVISIONS', 2));

        // Cleanup WordPress image edits.
        define('IMAGE_EDIT_OVERWRITE', env('IMAGE_EDIT_OVERWRITE', true));

        // Prevent file edititing from the dashboard.
        define('DISALLOW_FILE_EDIT', env('DISALLOW_FILE_EDIT', true));

        // Disable technical issues emails.
        // https://make.wordpress.org/core/2019/04/16/fatal-error-recovery-mode-in-5-2/
        define('WP_DISABLE_FATAL_ERROR_HANDLER', env('WP_DISABLE_FATAL_ERROR_HANDLER', false));

        // Set the cache constant for plugins such as WP Super Cache and W3 Total Cache.
        define('WP_CACHE', env('WP_CACHE', true));


        define( 'AS3CF_SETTINGS', serialize( array(
            'provider' => env('AS3CF_PROVIDER', 'do'),
            'access-key-id' => env('AS3CF_KEYID'),
            'secret-access-key' => env('AS3CF_SECRETKEY'),
            'bucket' => env('AS3CF_BUCKET'),
            'region' => env('AS3CF_REGION', 'nyc3'),
            'copy-to-s3' => true,
            'enable-object-prefix' => true,
            'object-prefix' => 'media/',
            'use-yearmonth-folders' => true,
            'object-versioning' => true,
            'delivery-provider' => env('AS3CF_DELIVER_PROVIDER','storage'),
            'serve-from-s3' => true,
            'enable-delivery-domain' => env('AS3CF_DELIVERY_DOMAIN',),
            'delivery-domain' => env('AS3CF_DELIVER_DOMAIN'),
            'force-https' => env('AS3CF_HTTPS'),
            'remove-local-file' => env('AS3CF_REMOVE_LOCAL'),
        ) ) );
        // Set the absolute path to the WordPress directory.
        if (!defined('ABSPATH')) {
            define('ABSPATH', sprintf('%s/%s/', $this->getPublicPath(), env('WP_DIR', 'wordpress')));
        }
        if (!empty($_ENV['PLATFORM_RELATIONSHIPS']) && extension_loaded('redis')) {
            $relationships = json_decode(base64_decode($_ENV['PLATFORM_RELATIONSHIPS']), true);

            $relationship_name = 'redis';

            if (!empty($relationships[$relationship_name][0])) {
                $redis = $relationships[$relationship_name][0];
                define('WP_REDIS_CLIENT', 'pecl');
                define('WP_REDIS_HOST', $redis['host']);
                define('WP_REDIS_PORT', $redis['port']);
            }
        }
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getPublicPath(): string
    {
        if (is_null($this->publicPath)) {
            return $this->basePath . DIRECTORY_SEPARATOR . 'public';
        }

        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath)
    {
        $this->publicPath = $publicPath;
    }
}
