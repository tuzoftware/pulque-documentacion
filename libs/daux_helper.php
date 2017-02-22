<?php
    namespace Todaymade\Daux;

    class DauxHelper {

        public static function get_breadcrumb_title_from_request($request, $separator = 'Chevrons', $multilanguage = false) {

            $request = str_replace('_', ' ', $request);
            switch ($separator) {
                case 'Chevrons':
                    $request = str_replace('/', ' <i class="glyphicon glyphicon-chevron-right"></i> ', $request);
                    return $request;
                case 'Colons':
                    $request = str_replace('/', ': ', $request);
                    return $request;
                case 'Spaces':
                    $request = str_replace('/', ' ', $request);
                    return $request;
                default:
                    $request = str_replace('/', $separator, $request);
                    return $request;
            }
            return $request;
        }

        public static function get_title_from_file($file) {
            $file = static::pathinfo($file);
            return static::get_title_from_filename($file['filename']);
        }

        public static function get_title_from_filename($filename) {
            $filename = explode('_', $filename);
            if ($filename[0] == '' || is_numeric($filename[0])) unset($filename[0]);
            else {
                $t = $filename[0];
                if ($t[0] == '-') $filename[0] = substr($t, 1);                
            }
            $filename = implode(' ', $filename);
            return $filename;
        }

        public static function get_url_from_file($file) {
            $file = static::pathinfo($file);
            return static::get_url_from_filename($file['filename']);
        }

        public static function get_url_from_filename($filename) {
            $filename = explode('_', $filename);
            if ($filename[0] == '' || is_numeric($filename[0])) unset($filename[0]);
            else {
                $t = $filename[0];
                if ($t[0] == '-') $filename[0] = substr($t, 1);                
            }
            $filename = implode('_', $filename);
            return $filename;
        }

        public static function build_directory_tree($dir, $ignore, $mode) {
            return static::directory_tree_builder($dir, $ignore, $mode);
        }

        public static function get_request_from_url($url, $base_url) {
            $url = substr($url, strlen($base_url) + 1);
            if (strpos($url, 'index.php') === 0) {
                $request = (($i = strpos($url, 'request=')) !== false) ? $request = substr($url, $i + 8) : '';
                if ($end = strpos($request, '&')) $request = substr($request, 0, $end);
                $request = ($request === '') ? 'index' : $request;
            } else {
                $request = ($url == '') ? 'index' : $url;
                $request = ($end = strpos($request, '?')) ? substr($request, 0, $end) : $request;
            }
            return $request;
        }

        public static function configure_theme($theme, $base_url, $local_base, $theme_url, $mode = Daux::LIVE_MODE) {
            $theme = array("name" => $theme);
            $theme["css"] = $base_url."/css/temas/{$theme["name"]}.css";

            if ($mode === Daux::LIVE_MODE) {
                $theme['favicon'] = utf8_encode($base_url . 'img/favicon.png');

            } else {
                if (!isset($theme['favicon'])) $theme['favicon'] = '<base_url>img/favicon.png';
                if (!isset($theme['css'])) $theme['css'] = array();
                if (!isset($theme['fonts'])) $theme['fonts'] = array();
                if (!isset($theme['js'])) $theme['js'] = array();
            }

            if (!isset($theme['template'])) $theme['template'] = $local_base . DIRECTORY_SEPARATOR . 'templates' .
                DIRECTORY_SEPARATOR . 'default.tpl';
            else $theme['template'] = str_replace('<local_base>', $local_base, $theme['template']);
            if (!isset($theme['error-template'])) $theme['error-template'] = $local_base . DIRECTORY_SEPARATOR . 'templates' .
                DIRECTORY_SEPARATOR . 'error.tpl';
            else $theme['error-template'] = str_replace('<local_base>', $local_base, $theme['error-template']);

            return $theme;
        }

        public static function rebase_theme($theme, $base_url, $theme_url) {
            $theme['favicon'] = utf8_encode(str_replace('<base_url>', $base_url, $theme['favicon']));
            $theme['favicon'] = str_replace('<theme_url>', $theme_url, $theme['favicon']);
            foreach ($theme['css'] as $key => $css) {
				$theme['css'][$key] = utf8_encode(str_replace('<base_url>', $base_url, $css));
                $theme['css'][$key] = utf8_encode(str_replace('<theme_url>', $theme_url, $css));
            }
            foreach ($theme['fonts'] as $key => $font) {
                $theme['fonts'][$key] = utf8_encode(str_replace('<base_url>', $base_url, $font));
                $theme['fonts'][$key] = utf8_encode(str_replace('<theme_url>', $theme_url, $font));

            }
            foreach ($theme['js'] as $key => $js) {
                $theme['js'][$key] = utf8_encode(str_replace('<base_url>', $base_url, $js));
                $theme['js'][$key] = utf8_encode(str_replace('<theme_url>', $theme_url, $js));
            }
            return $theme;
        }

        public static function google_analytics($analytics, $host) {
            $ga = '';
            return $ga;
        }

        public static function piwik_analytics($analytics_url, $analytics_id) {
            $pa = '';
            return $pa;
        }

        private static function directory_tree_builder($dir, $ignore, $mode = Daux::LIVE_MODE, $parents = null) {
            if ($dh = opendir($dir)) {
                $node = new Directory_Entry($dir, $parents);
                $new_parents = $parents;
                if (is_null($new_parents)) $new_parents = array();
                else $new_parents[] = $node;
                while (($entry = readdir($dh)) !== false) {
                    if ($entry == '.' || $entry == '..') continue;
                    $path = $dir . DIRECTORY_SEPARATOR . $entry;
                    if (is_dir($path) && in_array($entry, $ignore['folders'])) continue;
                    if (!is_dir($path) && in_array($entry, $ignore['files'])) continue;

                    $file_details = static::pathinfo($path);
                    if (is_dir($path)) $entry = static::directory_tree_builder($path, $ignore, $mode, $new_parents);
                    else if (in_array($file_details['extension'], Daux::$VALID_MARKDOWN_EXTENSIONS))
                    {
                        $entry = new Directory_Entry($path, $new_parents);
                        if ($mode === Daux::STATIC_MODE) $entry->uri .= '.html';
                    }
                    if ($entry instanceof Directory_Entry) $node->value[$entry->uri] = $entry; 
                }
                $node->sort();
                $node->first_page = $node->get_first_page();
                $index_key = ($mode === Daux::LIVE_MODE) ? 'index' : 'index.html';
                if (isset($node->value[$index_key])) {
                    $node->value[$index_key]->first_page = $node->first_page;
                    $node->index_page =  $node->value[$index_key];
                } else $node->index_page = false;
                return $node;
            }
        }

        public static function pathinfo($path) {
            preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $path, $m);
            if (isset($m[1])) $ret['dir']=$m[1];
            if (isset($m[2])) $ret['basename']=$m[2];
            if (isset($m[5])) $ret['extension']=$m[5];
            if (isset($m[3])) $ret['filename']=$m[3];
            return $ret;
        }

        public static function clean_copy_assets($path, $local_base){
            @mkdir($path);
            static::clean_directory($path);

            @mkdir($path . DIRECTORY_SEPARATOR . 'img');
            static::copy_recursive($local_base . DIRECTORY_SEPARATOR . 'img', $path . DIRECTORY_SEPARATOR . 'img');
            @mkdir($path . DIRECTORY_SEPARATOR . 'js');
            static::copy_recursive($local_base . DIRECTORY_SEPARATOR . 'js', $path . DIRECTORY_SEPARATOR . 'js');
            @mkdir($path . DIRECTORY_SEPARATOR . 'themes');
            static::copy_recursive($local_base . DIRECTORY_SEPARATOR . 'themes', $path . DIRECTORY_SEPARATOR . 'themes');
			@mkdir($path . DIRECTORY_SEPARATOR . 'font');
            static::copy_recursive($local_base . DIRECTORY_SEPARATOR . 'font', $path . DIRECTORY_SEPARATOR . 'font');
			@mkdir($path . DIRECTORY_SEPARATOR . 'css');
            static::copy_recursive($local_base . DIRECTORY_SEPARATOR . 'css', $path . DIRECTORY_SEPARATOR . 'css');
        }

        //  Rmdir
        private static function clean_directory($dir) {
            $it = new \RecursiveDirectoryIterator($dir);
            $files = new \RecursiveIteratorIterator($it,
                \RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file) {
                if ($file->getFilename() === '.' || $file->getFilename() === '..') continue;
                if ($file->isDir()) rmdir($file->getRealPath());
                else unlink($file->getRealPath());
            }
        }

        private static function copy_recursive($src,$dst) {
            $dir = opendir($src);
            @mkdir($dst);
            while(false !== ( $file = readdir($dir)) ) {
                if (( $file != '.' ) && ( $file != '..' )) {
                    if ( is_dir($src . '/' . $file) ) {
                        static::copy_recursive($src . '/' . $file,$dst . '/' . $file);
                    }
                    else {
                        copy($src . '/' . $file,$dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
        }

    }

    if (!function_exists('http_response_code')) {
        function http_response_code($code = NULL) {

            if ($code !== NULL) {

                switch ($code) {
                    case 100: $text = 'Continue'; break;
                    case 101: $text = 'Switching Protocols'; break;
                    case 200: $text = 'OK'; break;
                    case 201: $text = 'Created'; break;
                    case 202: $text = 'Accepted'; break;
                    case 203: $text = 'Non-Authoritative Information'; break;
                    case 204: $text = 'No Content'; break;
                    case 205: $text = 'Reset Content'; break;
                    case 206: $text = 'Partial Content'; break;
                    case 300: $text = 'Multiple Choices'; break;
                    case 301: $text = 'Moved Permanently'; break;
                    case 302: $text = 'Moved Temporarily'; break;
                    case 303: $text = 'See Other'; break;
                    case 304: $text = 'Not Modified'; break;
                    case 305: $text = 'Use Proxy'; break;
                    case 400: $text = 'Bad Request'; break;
                    case 401: $text = 'Unauthorized'; break;
                    case 402: $text = 'Payment Required'; break;
                    case 403: $text = 'Forbidden'; break;
                    case 404: $text = 'Not Found'; break;
                    case 405: $text = 'Method Not Allowed'; break;
                    case 406: $text = 'Not Acceptable'; break;
                    case 407: $text = 'Proxy Authentication Required'; break;
                    case 408: $text = 'Request Time-out'; break;
                    case 409: $text = 'Conflict'; break;
                    case 410: $text = 'Gone'; break;
                    case 411: $text = 'Length Required'; break;
                    case 412: $text = 'Precondition Failed'; break;
                    case 413: $text = 'Request Entity Too Large'; break;
                    case 414: $text = 'Request-URI Too Large'; break;
                    case 415: $text = 'Unsupported Media Type'; break;
                    case 500: $text = 'Internal Server Error'; break;
                    case 501: $text = 'Not Implemented'; break;
                    case 502: $text = 'Bad Gateway'; break;
                    case 503: $text = 'Service Unavailable'; break;
                    case 504: $text = 'Gateway Time-out'; break;
                    case 505: $text = 'HTTP Version not supported'; break;
                    default:
                        exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                header($protocol . ' ' . $code . ' ' . $text);
                $GLOBALS['http_response_code'] = $code;

            } else {
                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
            }
            return $code;
        }
    }


?>