<?php
    namespace Todaymade\Daux;
    class Template {

        private function get_navigation($tree, $path, $base_page) {
            $nav = '<ul class="nav nav-list primera"><li><a href="/index"><i class="i-home"></i> Inicio</a></li>';
            $nav .= $this->build_navigation($tree, $path, $base_page);
            $nav .= '</ul>';
            return $nav;
        }

        private function build_navigation($tree, $path, $base_page, $pad = 0) {
            $nav = '';
            foreach ($tree->value as $node) {
                $url = $node->uri;
                if ($node->type === \TodayMade\Daux\Directory_Entry::FILE_TYPE) {
                    if ($node->value === 'index') continue;
                    $nav .= '<li';
                    $link = ($path === '') ? $url : $path . '/' . $url;
                    $nav .= '><a style="padding-left:'.(15+($pad * 20)).'px" href="' . $base_page . $link . '"><i class="i-doc3"></i> ' . $node->title . '</a></li>';
                } else {
                    $nav .= '<li';
                    $link = ($path === '') ? $url : $path . '/' . $url;
                    $nav .= ' class="sube"'; 
                    $nav .= ">";

                    if ($node->index_page) $nav .= '<a href="' . $base_page . $link . '" class="folder"><i class="i-folder"></i> ' .
                        $node->title . '</a>';
                    else $nav .= '<a href="#" class="aj-nav folder"><i class="i-folder"></i> ' . $node->title . '</a>';
                    $nav .= '<ul class="nav nav-list">';
                    $new_path = ($path === '') ? $url : $path . '/' . $url;
                    $nav .= $this->build_navigation($node, $new_path, $base_page,  $pad+1);
                    $nav .= '</ul></li>';
                }
            }
            return $nav;
        }

        public function get_content($page, $params) {
            $base_url = $params['base_url'];
            $base_page = $params['base_page'];
            $project_title = utf8_encode($params['title']);
            $index = utf8_encode($base_page . $params['index']->value);
            $tree = $params['tree'];
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
    <link href="<?php echo $base_url ?>css/base-doc.css" rel='stylesheet' type='text/css'>
    <link href="<?php echo $base_url ?>css/fontello.css" rel='stylesheet' type='text/css'>
    <link href="<?php echo $base_url ?>css/animation.css" rel='stylesheet' type='text/css'>

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
                <div id="logo" class="col-sm-3 col-xs-6"><img src="/img/pulque.png"></div>
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


    <section id="documentation">
        <article class="boxed">
            <a href="#" class="btn btn-blanco docs-show hidden-print"><i class="i-docs"></i> Indice de temas</a>

            <!-- menu -->

            <nav id="docs" class="left-column article-tree hidden-print">
                <div id="sub-nav-collapse" class="sub-nav-collapse">
                    <?php
                    echo $this->get_navigation($tree, '', $base_page, $params['mode']);
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

                        <div class="page-header">
                            <h1>Página no encontrada</h1>
                        </div>
                
                        <?php echo $page['content']; ?>

                        <p>&nbsp;</p>
                        <img src="/img/error.jpg" class="img-responsive">
                        <p>&nbsp;</p>
                        
                    </article>
            </div>

        </article>
    </section>
    <!-- jQuery -->
    
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script>
        if (typeof jQuery == 'undefined')
            document.write(unescape("%3Cscript src='<?php echo $base_url; ?>js/jquery-1.11.0.min.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script src="<?php echo $base_url ?>js/bootstrap.min.js"></script>

    <!-- JS -->
    <script src="<?php echo $base_url ?>js/base.js"></script>

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