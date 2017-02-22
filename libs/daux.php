<?php
    namespace Todaymade\Daux;
    require_once(dirname(__FILE__) . '/../vendor/autoload.php');
    require_once('daux_directory.php');
    require_once('daux_helper.php');
    require_once('daux_page.php');


    class Daux
    {
        const STATIC_MODE = 'DAUX_STATIC';
        const LIVE_MODE = 'DAUX_LIVE';

        public static $VALID_MARKDOWN_EXTENSIONS;
        private $local_base;
        private $base_url;
        private $host;
        private $docs_path;
        private $tree;
        private $options;
        private $error_page;
        private $error = false;
        private $params;
        private $mode;

        function __construct($global_config_file = NULL) {
            $this->initial_setup($global_config_file);
        }

        public function initialize($config_file = 'config.json') {
            if ($this->error) return;
            $this->load_docs_config($config_file);
            $this->generate_directory_tree();
            if (!$this->error) $this->params = $this->get_page_params();
        }

        public function generate_static($output_dir = NULL) {
            if (is_null($output_dir)) $output_dir = $this->local_base . DIRECTORY_SEPARATOR . 'static';
            DauxHelper::clean_copy_assets($output_dir, $this->local_base);
            $this->recursive_generate_static($this->tree, $output_dir, $this->params);
        }

        public function handle_request($url, $query = array()) {
            if ($this->error) return $this->error_page;
            if (!$this->params['clean_urls']) $this->params['base_page'] .= 'index.php?request=';
            $request = DauxHelper::get_request_from_url($url, $this->base_url);
            $request = urldecode($request);
            $request_type = isset($query['method']) ? $query['method'] : '';
            switch ($request_type) {
                case 'DauxEdit':
                    if ($this->options['file_editor']) {
                        $content = isset($query['markdown']) ? $query['markdown'] : '';
                        return $this->save_file($request, $content);
                    }
                    return $this->generate_error_page('Editing Disabled', 'Editing is currently disabled in config',
                        ErrorPage::FATAL_ERROR_TYPE);
                default:
                    return $this->get_page($request);
            }
        }

        private function initial_setup($global_config_file) {
            $this->setup_environment_variables();
            $this->load_global_config($global_config_file);
        }

        private function setup_environment_variables() {
            global $argc;
            $this->local_base = dirname(dirname(__FILE__));
            $this->base_url = '';
            if (isset($argc)) {
                $this->mode = Daux::STATIC_MODE;
                return;
            }
            $this->mode = Daux::LIVE_MODE;
            $this->host = $_SERVER['HTTP_HOST'];
            $this->base_url = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            $this->base_url = substr($this->base_url, 0, strrpos($this->base_url, '/'));
        }

        private function load_global_config($global_config_file) {
            if (is_null($global_config_file)) $global_config_file = $this->local_base . DIRECTORY_SEPARATOR . 'global.json';
            if (!file_exists($global_config_file)) {
                $this->generate_error_page('Global Config File Missing',
                'The Global Config file is missing. Requested File : ' . $global_config_file, ErrorPage::FATAL_ERROR_TYPE);
                return;
            }
            $global_config = json_decode(file_get_contents($global_config_file), true);
            if (!isset($global_config)) {
                $this->generate_error_page('Corrupt Global Config File',
                    'The Global Config file is corrupt. Check that the JSON encoding is correct', ErrorPage::FATAL_ERROR_TYPE);
                return;
            }
            if (!isset($global_config['docs_directory'])) {
                $this->generate_error_page('Docs Directory not set', 'The Global Config file does not have the docs directory set.',
                    ErrorPage::FATAL_ERROR_TYPE);
                return;
            }
            $this->docs_path = $this->local_base . DIRECTORY_SEPARATOR . $global_config['docs_directory'];
            if (!is_dir($this->docs_path)) {
                $this->generate_error_page('Docs Directory not found',
                    'The Docs directory does not exist. Check the path again : ' . $this->docs_path, ErrorPage::FATAL_ERROR_TYPE);
                return;
            }
            if (!isset($global_config['valid_markdown_extensions'])) static::$VALID_MARKDOWN_EXTENSIONS = array('md', 'markdown');
            else static::$VALID_MARKDOWN_EXTENSIONS = $global_config['valid_markdown_extensions'];


        }

        private function load_docs_config($config_file) {
            $config_file = $this->docs_path . DIRECTORY_SEPARATOR . $config_file;
            if (!file_exists($config_file)) {
                $this->generate_error_page('Config File Missing',
                    'The local config file is missing. Check path : ' . $config_file, ErrorPage::FATAL_ERROR_TYPE);
                return;
            }

            $this->options = json_decode(file_get_contents($this->local_base . DIRECTORY_SEPARATOR . 'default.json'), true);
            if (is_file($config_file)) {
                $config = json_decode(file_get_contents($config_file), true);
                if (!isset($config)) {
                    $this->generate_error_page('Invalid Config File',
                        'There was an error parsing the Config file. Please review', ErrorPage::FATAL_ERROR_TYPE);
                    return;
                }
                $this->options = array_merge($this->options, $config);
            }
            if (isset($this->options['timezone'])) date_default_timezone_set($this->options['timezone']);
            else if (!ini_get('date.timezone')) date_default_timezone_set('GMT');
        }

        private function generate_directory_tree() {
            $this->tree = DauxHelper::build_directory_tree($this->docs_path, $this->options['ignore'], $this->mode);
            if (!empty($this->options['languages'])) {
                foreach ($this->options['languages'] as $key => $node) {
                    $this->tree->value[$key]->title = $node;
                }
            }
        }

        private function recursive_generate_static($tree, $output_dir, $params, $base_url = '') {
            $params['base_url'] = $params['base_page'] = $base_url;
            $new_params = $params;
            $params['theme'] = DauxHelper::rebase_theme($params['theme'], $base_url, $params['base_url'] . "themes/" . $params['theme']['name'] . '/');
            $params['image'] = str_replace('<base_url>', $base_url, $params['image']);
            if ($base_url !== '') $params['entry_page'] = $tree->first_page;
            foreach ($tree->value as $key => $node) {
                if ($node->type === Directory_Entry::DIRECTORY_TYPE) {
                    $new_output_dir = $output_dir . DIRECTORY_SEPARATOR . $key;
                    @mkdir($new_output_dir);
                    $this->recursive_generate_static($node, $new_output_dir, $new_params, '../' . $base_url);
                } else {
                    $params['request'] = $node->get_url();
                    $params['file_uri'] = $node->name;

                    $page = MarkdownPage::fromFile($node, $params);
                    file_put_contents($output_dir . DIRECTORY_SEPARATOR . $key, "\xEF\xBB\xBF".$page->get_page_content());
                }
            }
        }

        private function save_file($request, $content) {
            $file = $this->get_file_from_request($request);
            if ($file === false) return $this->generate_error_page('Page Not Found',
                'Usted está intentando acceder a información que no existe o que se encuentra en desarrollo. Porfavor revise la URL o intentelo más tarde.', ErrorPage::MISSING_PAGE_ERROR_TYPE);
            if ($file->write($content)) return new SimplePage('Success', 'Successfully Edited');
            else return $this->generate_error_page('File Not Writable', 'The file you wish to write to is not writable.',
                ErrorPage::FATAL_ERROR_TYPE);
        }

        private function generate_error_page($title, $content, $type) {
            $this->error_page = new ErrorPage($title, $content, $this->get_page_params($type));
            $this->error = true;
            return $this->error_page;
        }

        private function get_page($request) {
            $params = $this->params;
            $file = $this->get_file_from_request($request);
            if ($file === false) return $this->generate_error_page('Page Not Found',
                'Usted está intentando acceder a información que no existe o que se encuentra en desarrollo. Porfavor revise la URL o intentelo más tarde.', ErrorPage::MISSING_PAGE_ERROR_TYPE);
            $params['request'] = $request;
            $params['file_uri'] = $file->value;
            if ($request !== 'index') $params['entry_page'] = $file->first_page;
            return MarkdownPage::fromFile($file, $params);
        }


        private function imagen()
        {
            if(file_exists("img/app.png"))
            {  
                $imagen = "img/".filectime("img/app.png").".png";

                if(!file_exists($imagen))
                {
                    $marco = imagecreatefrompng("img/plantilla_app.png");
                    $fondo = imagecreatefrompng("img/app.png");
                    
                    $xSize = imagesx($marco);
                    $ySize = imagesy($marco);

                    $newPicture = imagecreatetruecolor( $xSize, $ySize );  
                    imageAlphaBlending($newPicture, true);                  
                    imagesavealpha( $newPicture, true );

                    imagecopyresampled( $newPicture, $fondo, 189, 52, 0, 0, $xSize, $ySize, $xSize, $ySize);
                    imagecopyresampled( $newPicture, $marco, 0, 0, 0, 0, $xSize, $ySize, $xSize, $ySize);

                    imagepng($newPicture, $imagen);
                }

                return $imagen;
            }

            return FALSE;
        }


        private function hex_a_rgb($hex)
        {
           $hex = str_replace("#", "", $hex);

           if(strlen($hex) == 3) {
              $r = hexdec(substr($hex,0,1).substr($hex,0,1));
              $g = hexdec(substr($hex,1,1).substr($hex,1,1));
              $b = hexdec(substr($hex,2,1).substr($hex,2,1));
           } else {
              $r = hexdec(substr($hex,0,2));
              $g = hexdec(substr($hex,2,2));
              $b = hexdec(substr($hex,4,2));
           }
           $rgb = array($r, $g, $b);

           return implode(",", $rgb);
        }        

        private function get_page_params($mode = '') {
            $params = array();
            $params['local_base'] = $this->local_base;
       
            $params['image'] = $this->imagen();
            $params['color'] = $this->hex_a_rgb($this->options['color']);
        
            if ($mode === '') $mode = $this->mode;
            $params['mode'] = $mode;
            switch ($mode) {
                case ErrorPage::FATAL_ERROR_TYPE:
                    $params['error_type'] = ErrorPage::FATAL_ERROR_TYPE;
                    break;

                case ErrorPage::NORMAL_ERROR_TYPE:
                case ErrorPage::MISSING_PAGE_ERROR_TYPE:
                    $params['error_type'] = $mode;
                    $params['index_key'] = 'index';
                    $params['docs_path'] = $this->docs_path;
                    $protocol = '//';
                    $params['base_url'] = $protocol . $this->base_url . '/';
                    $params['base_page'] = $params['base_url'];
                    $params['host'] = $this->host;
                    $params['tree'] = $this->tree;
                    $params['index'] = ($this->tree->index_page !== false) ? $this->tree->index_page : $this->tree->first_page;
                    $params['clean_urls'] = true;

                    $params['tagline'] = $this->options['tagline'];
                    $params['title'] = $this->options['title'];
                    $params['author'] = $this->options['author'];
                    $params['version'] = $this->options['version'] ? "Versión ".$this->options['version'] : "";
                    if ($params['image'] !== '') $params['image'] = str_replace('<base_url>', $params['base_url'], $params['image']);
                    $params['repo'] = $this->options['repo'];
                    $params['links'] = $this->options['links'];
                    $params['menu'] = $this->html_menu($this->options['menu']);
                    $params['twitter'] = $this->options['twitter'];
                    $params['google_analytics'] =  '';
                    $params['piwik_analytics'] =  '';

                    $params['template'] = $this->options['template'];
                    $params['theme'] = DauxHelper::configure_theme($this->options['theme'], $params['base_url'],
                        $this->local_base, $params['base_url'] . "css/temas/", $mode);
                    break;

                case Daux::LIVE_MODE:
                    $params['docs_path'] = $this->docs_path;
                    $params['index_key'] = 'index';
                    $protocol = '//';
                    $params['base_url'] = $protocol . $this->base_url . '/';
                    $params['base_page'] = $params['base_url'];
                    $params['host'] = $this->host;
                    $params['tree'] = $this->tree;
                    $params['index'] = ($this->tree->index_page !== false) ? $this->tree->index_page : $this->tree->first_page;
                    $params['clean_urls'] = true;
                    $params['version'] = $this->options['version'] ? "Versión ".$this->options['version'] : "";

                    $params['tagline'] = $this->options['tagline'];
                    $params['title'] = $this->options['title'];
                    $params['author'] = $this->options['author'];
                    if ($params['image'] !== '') $params['image'] = str_replace('<base_url>', $params['base_url'], $params['image']);
                    $params['repo'] = $this->options['repo'];
                    $params['links'] = $this->options['links'];
                    $params['twitter'] = $this->options['twitter'];
                    $params['menu'] = $this->html_menu($this->options['menu']);
                    $params['google_analytics'] = '';
                    $params['piwik_analytics'] = '';

                    $params['template'] = $this->options['template'];
                    $params['theme'] = DauxHelper::configure_theme($this->options['theme'], $params['base_url'],
                        $this->local_base, $params['base_url'] . "css/temas/", $mode);


                    if ($params['breadcrumbs'] = 1)
                        $params['breadcrumb_separator'] = "<i class=\"i-derecha\"></i>";
                    $params['multilanguage'] = false;
                    $params['entry_page'] = $this->tree->first_page;
                    $params['toggle_code'] = $this->options['toggle_code'];
                    $params['float'] = $this->options['float'];
                    $params['date_modified'] = $this->options['date_modified'];
                    $params['file_editor'] = $this->options['file_editor'];
                    break;

                case Daux::STATIC_MODE:
                    $params['docs_path'] = $this->docs_path;
                    $params['index_key'] = 'index.html';
                    $params['base_url'] = '';
                    $params['base_page'] = $params['base_url'];
                    $params['tree'] = $this->tree;
                    $params['index'] = ($this->tree->index_page !== false) ? $this->tree->index_page : $this->tree->first_page;
                    $params['version'] = $this->options['version'] ? "Versión ".$this->options['version'] : "";
                    $params['tagline'] = $this->options['tagline'];
                    $params['title'] = $this->options['title'];
                    $params['author'] = $this->options['author'];
                    $params['repo'] = $this->options['repo'];
                    $params['menu'] = $this->html_menu($this->options['menu']);
                    $params['links'] = $this->options['links'];
                    $params['twitter'] = $this->options['twitter'];
                    $params['google_analytics'] = ($g = $this->options['google_analytics']) ?
                        DauxHelper::google_analytics($g, $this->host) : '';
                    $params['piwik_analytics'] = ($p = $this->options['piwik_analytics']) ?
                        DauxHelper::piwik_analytics($p, $this->options['piwik_analytics_id']) : '';

                    $params['theme'] = DauxHelper::configure_theme($this->options['theme'], $params['base_url'],
                        $this->local_base, $params['base_url'] . "css/temas/", $mode);      

                    if ($params['breadcrumbs'] = 1)
                        $params['breadcrumb_separator'] = "<i class=\"i-derecha\"></i>";
                 
                    $params['entry_page'] = $this->tree->first_page;

                    $params['toggle_code'] = $this->options['toggle_code'];
                    $params['float'] = $this->options['float'];
                    $params['date_modified'] = $this->options['date_modified'];
                    $params['file_editor'] = false;
                    break;
            }

            return $params;
        }

        private function get_file_from_request($request) {
            $file = $this->tree->retrieve_file($request);
            return $file;
        }

        private function html_menu($array)
        {            
            if(count($array))
            {
                $html = "\n<div class=\"collapse navbar-collapse\" id=\"bs-example-navbar-collapse-1\"><ul class=\"nav navbar-nav navbar-right\">";

                foreach($array as $key => $opcion)
                {
                    if(is_array($opcion))
                    {
                        $html .= "\n<li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">{$key} <span class=\"caret\"></span></a><ul class=\"dropdown-menu\" role=\"menu\">";
                        
                        foreach($opcion as $key2 => $opcion2)
                        {
                            if($key2 == "DIVIDE")
                            {
                                $html .= "\n<li class=\"divider\"></li>";
                            }
                            else
                            {
                                $html .= "\n<li><a href=\"{$opcion2}\">{$key2}</a></li>";
                            }
                        }
                        $html .= "</ul></li>";
                    }
                    else
                    {
                        $html .= "\n<li><a href=\"{$opcion}\">{$key}</a></li>";
                    }
                }

                return $html."</ul></div>";
            }
            else
                return 0;
        }
    }

    ?>