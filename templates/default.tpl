<?php
    namespace Todaymade\Daux;
    class Template {

        private function get_navigation($tree, $path, $current_url, $base_page, $mode,$repo) {
            $nav = '<ul class="nav nav-list primera"><li><a href="'.$repo.'"><i class="i-home"></i> Inicio</a></li>';
            $nav .= $this->build_navigation($tree, $path, $current_url, $base_page, $mode);
            $nav .= '</ul>';
            return $nav;
        }

        private function build_navigation($tree, $path, $current_url, $base_page, $mode, $pad = 0) {
            $nav = '';
            foreach ($tree->value as $node) {
            	$url = $node->uri;
                if ($node->type === \TodayMade\Daux\Directory_Entry::FILE_TYPE) {
                    if ($node->value === 'index') continue;
                    $nav .= '<li';
                    $link = ($path === '') ? $url : $path . '/' . $url;
                    if ($current_url === $link) $nav .= ' class="open2"';
                    $nav .= '><a style="padding-left:'.(15+($pad * 20)).'px" href="' . $base_page . $link . '"><i class="i-doc3"></i> ' . $node->title . '</a></li>';
                } else {
                    $nav .= '<li';
                    $link = ($path === '') ? $url : $path . '/' . $url;
                    if (strpos($current_url, $link) === 0) $nav .= ' class="open"'; else $nav .= ' class="sube"'; 
                    $nav .= ">";
                    if ($mode === \TodayMade\Daux\Daux::STATIC_MODE) $link .= "/index.html";
                    if ($node->index_page) $nav .= '<a href="' . $base_page . $link . '" class="folder"><i class="i-folder"></i> ' .
                        $node->title . '</a>';
                    else $nav .= '<a href="#" class="aj-nav folder"><i class="i-folder"></i> ' . $node->title . '</a>';
                    $nav .= '<ul class="nav nav-list">';
                    $new_path = ($path === '') ? $url : $path . '/' . $url;
                    $nav .= $this->build_navigation($node, $new_path, $current_url, $base_page, $mode, $pad+1);
                    $nav .= '</ul></li>';
                }
            }
            return $nav;
        }

        private function get_breadcrumb_title($page, $base_page) {
            $title = '';
            $breadcrumb_trail = $page['breadcrumb_trail'];
            $separator = $this->get_separator($page['breadcrumb_separator']);
            foreach ($breadcrumb_trail as $key => $value) {
                $title .= '<a href="' . $base_page . $value . '">' . $key . '</a>' . $separator;
            }
            if ($page['filename'] === 'index' || $page['filename'] === '_index') {
                if ($page['title'] != '') $title = substr($title, 0, -1 * strlen($separator));
            } else $title .= '<a href="' . $base_page . $page['request'] . '">' . $page['title'] . '</a>';
            return $title;
        }

        private function get_separator($separator) {
            switch ($separator) {
                case 'Chevrons':
                    return ' <i class="glyphicon glyphicon-chevron-right"></i> ';
                default:
                    return $separator;
            }
        }

        public function get_content($page, $params) {
            $base_url = $params['base_url'];
            $base_page = $params['base_page'];
            $homepage = $page['homepage'];
            $project_title = utf8_encode($params['title']);
            $index = utf8_encode($base_page . $params['index']->value);
            $tree = $params['tree'];
            $entry_page = $page['entry_page'];
            ob_start();
?>
<!DOCTYPE html>
<!--[if lt IE 7]>       <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>          <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>          <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->  <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <title><?php echo $page['title']; ?></title>
    <meta name="description" content="<?php echo $page['tagline'];?>" />
    <meta name="author" content="<?php echo $page['author']; ?>">
    <link rel="icon" href="<?php echo $page['theme']['favicon']; ?>" type="image/x-icon">
    <!-- Mobile -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link href="<?php echo $base_url ?>css/bootstrap.css"   rel='stylesheet' type='text/css'>
    <link href="<?php echo $base_url ?>css/base-doc.css"       rel='stylesheet' type='text/css'>
    <link href="<?php echo $base_url ?>css/fontello.css"    rel='stylesheet' type='text/css'>
    <link href="<?php echo $base_url ?>css/animation.css"   rel='stylesheet' type='text/css'>

    <style type="text/css">
        .nav>li>a:hover,.nav>li>a:focus, .navbar{ background:rgba(<?php echo $params['color']; ?>,1); }
        .btn-primary .badge, .dropdown-menu>li>a:hover,.dropdown-menu>li>a:focus, a, #titulo h1{ color:rgba(<?php echo $params['color']; ?>,1); }
        a:hover{ color: rgba(<?php echo $params['color']; ?>,0.9); } 
        .btn-primary { background-color:rgba(<?php echo $params['color']; ?>,1); }
        .btn-primary:hover,.btn-primary:focus,.btn-primary:active,.btn-primary.active,.open .dropdown-toggle.btn-primary, .btn-blanco:hover,.btn-blanco:focus,.btn-blanco:active,.btn-blanco.active,.open .dropdown-toggle.btn-blanco { background-color:rgba(<?php echo $params['color']; ?>,0.9); }
        .btn-primary.disabled,.btn-primary[disabled],.btn-primary.disabled:hover,.btn-primary[disabled]:hover,.btn-primary.disabled:focus,.btn-primary[disabled]:focus,.btn-primary.disabled:active,.btn-primary[disabled]:active,.btn-primary.disabled.active,.btn-primary[disabled].active{ background-color: rgba(<?php echo $params['color']; ?>,0.6); }
        .ingreso{ background:rgba(<?php echo $params['color']; ?>,0.1); }
        .nav .open>a,.nav .open>a:hover,.nav .open>a:focus, .nav .open2>a,.nav .open2>a:hover,.nav .open2>a:focus, .btn-blanco{ background:rgba(<?php echo $params['color']; ?>,0.3); }
        #documentation nav#docs, code { background:rgba(<?php echo $params['color']; ?>,0.15); }        
        pre { background-color: rgba(<?php echo $params['color']; ?>,0.05); border: 1px solid rgba(<?php echo $params['color']; ?>,0.5); }
        .page-header { border-bottom: 1px solid rgba(<?php echo $params['color']; ?>,.5); }
        #documentation #docs-content h2 {border-top:1px solid rgba(<?php echo $params['color']; ?>,0.1);}        
    </style>

</head>
<body>

        <!-- Encabezado -->

        <div id="header">
            <div class="container"><div class="row">
                <div id="logo" class="col-sm-3 col-xs-6"><img src="<?php echo $params['repo'];?>img/pulque.png"></div>
                <div class="col-sm-9 col-xs-6" id="titulo">
                    <h1>
                        <span class="hidden-xs"><?php echo $params['tagline'] ?></span><span class="visible-xs"><?php echo $project_title; ?></span>
                    </h1>
                    <span id="version"><?php echo $params['version']; ?></span>
                </div>
                </div>
            </div>
        </div>

        <!-- Menu -->

        <nav class="navbar navbar-default col-xs-12" role="navigation">
          <div class="container">

            <div class="navbar-header">
                <?php if($params['menu']) echo "\n<button type=\"button\" class=\"navbar-toggle collapsed\" data-toggle=\"collapse\" data-target=\"#bs-example-navbar-collapse-1\"><span class=\"sr-only\">Toggle navigation</span><span class=\"icon-bar\"></span><span class=\"icon-bar\"></span><span class=\"icon-bar\"></span></button>"; ?>

                <span class="navbar-brand" href="/"><i class="i-doc3"></i> DOCUMENTACION</a>
            </div>

            <?php if($params['menu']) echo $params['menu']; ?>

          </div>
        </nav>

        <!-- Body -->

        <?php if ($homepage) { ?>
            <p>&nbsp;</p>

        <div class="container hero">
            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <?php if ($params['image']) echo '<img class="homepage-image img-responsive" src="/' . $params['image'] . '" alt="' . $project_title . '">'; ?>
                </div>
                <div class="text-center col-sm-12">
                    <?php if ($page['tagline']) echo '<h1 style="font-weight:bold;font-size:36px;margin:50px">' . $page['tagline'] . '</h1>'; ?>
                </div>
            </div>
        </div>

        <div class="container ingreso">
            <div class="row">
                <div class="text-center col-sm-12">
                    <?php
                        foreach ($entry_page as $key => $node) echo '<a href="'.$node.'" class="btn btn-primary">'.$key.'</a>';
                    ?>
                </div>
            </div>
        </div>
                        <p>&nbsp;</p>
                        <p>&nbsp;</p>

        <div class="homepage-footer">
            <div class="container">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2"><div class="row">
                        <?php if (!empty($params['links'])) { ?>
                            
                                <?php foreach ($params['links'] as $name => $url) echo '<div class="col-sm-6"><a class="btn btn-blanco btn-block" href="'.$url.'"><i class="i-link"></i> '.$name.'</a></div>'; ?>
                            
                        <?php } ?>
                    </div></div>
                </div>
            </div>
        </div>

        <?php } else { ?>

        <!-- Docs -->

    <section id="documentation">
        <article class="boxed">
            <a href="#" class="btn btn-blanco docs-show hidden-print"><i class="i-docs"></i> Indice de temas</a>

            <!-- menu -->

            <nav id="docs" class="left-column article-tree hidden-print">
                <div id="sub-nav-collapse" class="sub-nav-collapse">
                    <?php
                    echo $this->get_navigation($tree, '', $params['request'], $base_page, $params['mode'],$params['repo']);
                    if (!empty($params['links'])) { ?>
                    
                    <p>&nbsp;</p>
                    <div class="col-xs-12">
                            <!-- Links -->
                            <?php foreach ($params['links'] as $name => $url) echo '<a class="btn btn-blanco btn-block" style="margin:-5px 0" href="' . $url . '" target="_blank">' . $name . '</a><br>'; ?>
                    </div>
                    <?php } ?>
                </div>
            </nav>

            <!-- contenido -->

            <div id="docs-content">
                    <article>
                        <?php if ($params['date_modified']) { ?>
                            <div class="page-header sub-header clearfix">
                                <h1><?php
                                        if ($page['breadcrumbs']) echo $this->get_breadcrumb_title($page, $base_page);
                                        else echo $page['title'];
                                    ?>
                                    <?php if ($page['file_editor']) echo '<a href="javascript:;" id="editThis" class="btn">Edit this page</a>'; ?>
                                </h1>
                                <span style="float: left; font-size: 10px; color: gray;">
                                    <?php echo date("l, F j, Y", $page['modified_time']);?>
                                </span>
                                <span style="float: right; font-size: 10px; color: gray;">
                                    <?php echo date("g:i A", $page['modified_time']);?>
                                </span>
                            </div>
                        <?php } else { ?>
                            <div class="page-header">
                                <h1><?php
                                        if ($page['breadcrumbs']) echo $this->get_breadcrumb_title($page, $base_page);
                                        else echo $page['title'];
                                    ?>
                                    <?php if ($page['file_editor']) echo '<a href="javascript:;" id="editThis" class="btn">Edit this page</a>'; ?>                                    </h1>
                            </div>
                        <?php } ?>

                        <?php echo $page['content']; ?>
                        <?php if ($page['file_editor']) { ?>
                            <div class="editor<?php if(!$params['date_modified']) echo ' paddingTop'; ?>">
                                <h3>You are editing <?php echo $page['path']; ?>&nbsp;<a href="javascript:;" class="closeEditor btn btn-warning">Close</a></h3>
                                <div class="navbar navbar-inverse navbar-default navbar-fixed-bottom" role="navigation">
                                    <div class="navbar-inner">
                                        <a href="javascript:;" class="save_editor btn btn-primary navbar-btn pull-right">Save file</a>
                                    </div>
                                </div>
                                <textarea id="markdown_editor"><?php echo $page['markdown'];?></textarea>
                                <div class="clearfix"></div>
                            </div>
                        <?php } ?>
                    </article>
            </div>

        </article>
    </section>

    <?php 
    } 
    ?>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script>
        if (typeof jQuery == 'undefined')
            document.write(unescape("%3Cscript src='<?php echo $base_url; ?>js/jquery-1.11.0.min.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script src="<?php echo $base_url ?>js/bootstrap.min.js"></script>

    <!-- hightlight.js -->
    <script src="<?php echo $base_url; ?>js/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>

    <!-- JS -->
    <script src="<?php echo $base_url ?>js/base.js"></script>

    <!-- Front end file editor -->
    <?php if ($page['file_editor']) echo '<script src="'. $base_url. 'js/editor.js"></script>'; ?>
    <script src="<?php echo $base_url; ?>js/custom.js"></script>
    <!--[if lt IE 9]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

</body>
</html>

<?php
            $return = ob_get_contents();
            @ob_end_clean();
            return $return;
        }
    }
?>