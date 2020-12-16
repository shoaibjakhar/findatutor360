<!DOCTYPE html>
<html lang="en" dir="<?php echo language_type();?>">

<head>
    <title>
    <?php 
    if(isset($pagetitle) && $pagetitle != '')
    echo $pagetitle.' - '. $this->config->item('site_settings')->site_title ;
    elseif(isset($this->config->item('site_settings')->site_title) && $this->config->item('site_settings')->site_title != '')
    echo $this->config->item('site_settings')->site_title;
    else
        echo get_languageword('Tutors').' : '.get_languageword('Find Tutors Now');
    ?></title>

        <!-- Prevent the demo from appearing in search engines -->
        <meta name="robots"
              content="noindex">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <meta name="description" content="<?php if(isset($meta_description) && $meta_description != "") echo $meta_description; elseif(isset($this->config->item('seo_settings')->meta_description) && $this->config->item('seo_settings')->meta_description != '') echo $this->config->item('seo_settings')->meta_description; else if(isset($this->config->item('seo_settings')->site_description) && $this->config->item('seo_settings')->site_description != '') echo $this->config->item('seo_settings')->site_description;?>">

        <meta name="keywords" content="<?php if(isset($meta_keywords) && $meta_keywords != "") echo $meta_keywords; elseif(isset($this->config->item('seo_settings')->meta_keyword)) echo $this->config->item('seo_settings')->meta_keyword;?>">
        <meta name="author" content="">

        <link href="https://fonts.googleapis.com/css?family=Lato:400,700%7CRoboto:400,500%7CExo+2:600&display=swap"
              rel="stylesheet">

        <!-- Preloader -->
        <link type="text/css"
              href="<?php echo base_url(); ?>assets/public/vendor/spinkit.css"
              rel="stylesheet">

        <!-- Perfect Scrollbar -->
        <link type="text/css"
              href="<?php echo base_url(); ?>assets/public/vendor/perfect-scrollbar.css"
              rel="stylesheet">

        <!-- Material Design Icons -->
        <link type="text/css"
              href="<?php echo base_url(); ?>assets/public/css/material-icons.css"
              rel="stylesheet">

        <!-- Font Awesome Icons -->
        <link type="text/css"
              href="<?php echo base_url(); ?>assets/public/css/fontawesome.css"
              rel="stylesheet">

        <!-- Preloader -->
        <link type="text/css"
              href="<?php echo base_url(); ?>assets/public/css/preloader.css"
              rel="stylesheet">

        <!-- App CSS -->
        <link type="text/css"
              href="<?php echo base_url(); ?>assets/public/css/app.css"
              rel="stylesheet">

    </head>

    <body class="layout-sticky-subnav layout-default ">

        <div class="preloader">
            <div class="sk-chase">
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
                <div class="sk-chase-dot"></div>
            </div>

            <!-- <div class="sk-bounce">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div> -->

            <!-- More spinner examples at https://github.com/tobiasahlin/SpinKit/blob/master/examples.html -->
        </div>

        <!-- Header Layout -->
        <div class="mdk-header-layout js-mdk-header-layout">

            <!-- Header -->

            <div id="header"
                 class="mdk-header mdk-header--bg-dark bg-dark js-mdk-header mb-0"
                 data-effects="parallax-background waterfall"
                 data-fixed
                 data-condenses>
                <div class="mdk-header__bg">
                    <div class="mdk-header__bg-front"
                         style="background-image: url(../../public/images/photodune-4161018-group-of-students-m.jpg);"></div>
                </div>
                <div class="mdk-header__content justify-content-center">

                    <div class="navbar navbar-expand navbar-dark-pickled-bluewood bg-transparent will-fade-background"
                         id="default-navbar"
                         data-primary>

                        <!-- Navbar toggler -->
                        <!-- <button class="navbar-toggler w-auto mr-16pt d-block rounded-0"
                                type="button"
                                data-toggle="sidebar">
                            <span class="material-icons">short_text</span>
                        </button> -->

                        <!-- Navbar Brand -->
                        <a href="<?php echo base_url();?>"
                           class="navbar-brand mr-16pt">
                            <img class="navbar-brand-icon" src="<?php  if(isset($this->config->item('site_settings')->logo) && $this->config->item('site_settings')->logo != '') echo URL_PUBLIC_UPLOADS_SETTINGS.''.$this->config->item('site_settings')->logo; else echo URL_FRONT_IMAGES.'Logo.png';?>" class="logo <?php if($this->ion_auth->logged_in() && !empty($my_profile)) echo "dahboard-logo"; ?>"  alt="Luma">

                        </a>

                        <ul class="nav navbar-nav d-none d-sm-flex flex justify-content-start ml-8pt">
                            <li class="nav-item active">
                                <a href="<?php echo SITEURL;?>"
                                   class="nav-link <?php if(isset($activemenu) && $activemenu == "home") echo 'active'; ?>"><?php echo get_languageword('Home');?></a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="<?php echo URL_HOME_SEARCH_TUTOR;?>"
                                   class="<?php if(isset($activemenu) && $activemenu == "search_tutor") echo 'active'; ?> nav-link"><?php echo get_languageword('Find Tutor');?></a>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo URL_HOME_SEARCH_INSTITUTE;?>"
                                   class="<?php if(isset($activemenu) && $activemenu == "search_institute") echo 'active'; ?> nav-link"><?php echo get_languageword('Find Institute');?></a>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo URL_HOME_SEARCH_STUDENT_LEADS;?>" title="<?php echo get_languageword('find_student_leads'); ?>"
                                   class="<?php if(isset($activemenu) && $activemenu == "search_student_leads") echo 'active'; ?> nav-link"><?php echo get_languageword('Find Leads');?></a>
                            </li>


                            <?php if(!($this->ion_auth->is_tutor() || $this->ion_auth->is_institute() || $this->ion_auth->is_admin())) { ?>
                              <li class="nav-item">
                                  <a href="<?php echo URL_HOME_BUY_COURSES;?>"
                                     class="<?php if(isset($activemenu) && $activemenu == "buy_courses") echo 'active'; ?> nav-link"><?php echo get_languageword('Buy_Courses');?> </a>
                              </li>
                            <?php } ?>


                            <li class="nav-item dropdown">
                                <a href="#"
                                   class="<?php if(isset($activemenu) && $activemenu == "blog") echo 'active'; ?> nav-link dropdown-toggle"
                                   data-toggle="dropdown"
                                   data-caret="false"><?php echo get_languageword('pages');?></a>
                                <div class="dropdown-menu">
                                    <a href="<?php echo URL_HOME_ABOUT_US;?>"
                                       class="dropdown-item"><?php echo get_languageword('About Us');?> </a>
                                    <a href="<?php echo URL_HOME_FAQS;?>"
                                       class="dropdown-item"><?php echo get_languageword('FAQs');?> </a>
                                    <a href="<?php echo URL_HOME_CONTACT_US;?>"
                                       class="dropdown-item"><?php echo get_languageword('Contact Us');?> </a>
                                    <a href="<?php echo URL_HOME_LIST_BLOGS;?>"
                                       class="dropdown-item"> <?php echo get_languageword('blogs');?> </a>
                                    <a href="instructor-statement.html"
                                       class="dropdown-item">Statement</a>
                                    
                                    <?php 

                                       $pages_titles= $this->base_model->get_page_by_title();
                                       
                                       if (!empty($pages_titles)) {

                                         foreach($pages_titles as $row ){?>
                                         <a class="dropdown-item" href="<?php echo URL_BLOG_PAGES .'/'.$row->slug;?>"><?php echo $row->name;?></a>
                                         <?php }?>

                                          <?php if(!$this->ion_auth->logged_in()) { ?>
                                          <a class="dropdown-item" href="<?php echo URL_AUTH_LOGIN;?>"><?php echo get_languageword('Login');?></a>
                                       
                                    <?php } } ?>


                                </div>
                            </li>
                            
                        </ul>

                        <ul class="nav navbar-nav ml-auto mr-0">
                          <?php if (!$this->ion_auth->logged_in()) { ?>
                            <li class="nav-item">
                                <a href="<?php echo URL_AUTH_LOGIN;?>"
                                   class="nav-link"
                                   data-toggle="tooltip"
                                   data-title="<?php echo get_languageword('Login');?>"
                                   data-placement="bottom"
                                   data-boundary="window"><i class="material-icons">lock_open</i></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo URL_AUTH_LOGIN;?>"
                                   class="btn btn-outline-white">Get Started</a>
                            </li>
                          <?php } ?>
                        </ul>
                    </div>


                    <?php if(isset($activemenu) && $activemenu == "home") { ?>

                    <div class="hero container page__container text-center text-md-left py-112pt">
                        <h1 class="text-white text-shadow">Learn to Code</h1>
                        <p class="lead measure-hero-lead mx-auto mx-md-0 text-white text-shadow mb-48pt">Business, Technology and Creative Skills taught by industry experts. Explore a wide range of skills with our professional tutorials.</p>

                        <a href="<?php echo URL_HOME_ALL_COURSES; ?>"
                           class="btn btn-lg btn-white btn--raised mb-16pt">Browse Courses</a>

                        <!-- <p class="mb-0"><a href="" class="text-white text-shadow"><strong>Are you a teacher?</strong></a></p> -->

                    </div>
                  <?php } ?>
                </div>
            </div>

            <!-- // END Header -->