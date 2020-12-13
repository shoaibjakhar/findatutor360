<link rel="stylesheet" href="<?php echo URL_FRONT_CSS;?>blog.css">
<style>
.weekly-top-rated {
    background: #ffffff !important; 
    }

.owl-carousel.owl-loading {
    opacity: 1;
}    

@media only screen and (min-width: 1280px) {
    .owl-item {
        width: 290px !important;
    }
}

@media only screen and (min-width: 1280px) {
.owl-stage {
    width: 3705px !important;
    /*transform: translate3d(-1527px, 7px, 5px) !important; */
    }
}
</style>
    <!-- User Profile Details -->
 <?php  if (!empty($blog_details)) { ?>
   
    <div class="container">

        

        <div class="row-margin ">


     
           <div class="box-border">
                <div class="row ">
                    <!-- User Profile -->
                    <div class="col-lg-2 col-md-3 col-sm-3 col-xs-12">
                        <div class="profile-listing">
                            <a href="<?php echo URL_HOME_TUTOR_PROFILE.'/'.$blog_details->slug; ?>"><img src="<?php echo get_tutor_img($blog_details->photo, $blog_details->gender); ?>" alt="Tutor Profile Image" class="img-responsive img-circle"></a>
                        </div>

                        <a href="<?php echo URL_HOME_TUTOR_PROFILE.'/'.$blog_details->slug; ?>"><span class="tutor-name"><?php echo $blog_details->username;?></span></a>
                    </div>

                   

                    <div class="col-lg-9 col-md-8 col-sm-8 col-xs-12">
                        <div class="user-profile-content">
                            <h4 class="title"><a href="<?php echo URL_HOME_VIEW_BLOG_DETAILS.'/'.$blog_details->blog_id;?>"> <?php echo $blog_details->title; ?></a></h4>

                            <p><?php echo $blog_details->description;?> </p>
                        </div>
                    </div>
                </div>

            </div>
       
           

            <!---TUTOR BLOGS--->
           
           <?php if (!empty($blog_details->blogs)) { ?>

           
          <!-- Tutor blogs slider -->
            <section class="weekly-top-rated">
                <div class="container">
                    <div class="row row-margin">
                        <div class="col-md-12">

                            <h2 class="heading-border-btm"><?php if (isset($tutor_record)) echo $tutor_record->username;?> <span><?php echo get_languageword('blogs'); ?></span></h2>

                            <div class="toprated-slider owl-theme">

                            <?php foreach($blog_details->blogs as $blog) {
                                    $hlink = URL_HOME_TUTOR_PROFILE.'/'.$blog->slug;
                                ?>
                                <div class="item">
                                    <div class="profile-block">
                                        <div class="media-left">
                                            <div class="profile-img">
                                                <a href="<?php echo $hlink;?>">
                                                   <img src="<?php echo get_tutor_img($blog->photo, $blog->gender); ?>" alt="" class="img-circle"> 
                                                </a>
                                            </div>
                                        </div>
                                        <div class="media-body">
                                            <a href="<?php echo URL_HOME_VIEW_BLOG_DETAILS.'/'.$blog->blog_id; ?>">
                                                <h4 title="<?php echo $blog->title;?>"><?php echo substr($blog->title,0,15).'...';?></h4>
                                                <p><small><?php echo $blog->related_to;?></small></p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
            
                            </div>
                        </div>
                    </div>
                </div>
            </section>
             <?php } ?>   
            <!-- Tutor Blogs End-->
        
        </div>

    </div>

<script src="<?php echo URL_FRONT_JS;?>jquery.js"></script>



<link rel="stylesheet" href="<?php echo URL_FRONT_CSS;?>jquery.raty.css">
<script src="<?php echo URL_FRONT_JS;?>jquery.raty.js"></script>
<script>

    /****** Tutor Avg. Rating  ******/
   $('div.avg_rating, span.avg_rating').raty({

    path: '<?php echo RESOURCES_FRONT;?>raty_images',
    score: function() {
      return $(this).attr('data-score');
    },
    readOnly: true
   });

   
</script>

<?php } ?>
<!-- User Profile Details  -->