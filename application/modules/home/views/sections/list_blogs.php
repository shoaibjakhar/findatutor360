<?php 
	  if(!empty($blogs)): 
		foreach($blogs as $row): 
?>
<div class="box-border">
    <div class="row ">
        <!-- User Profile -->
        <div class="col-lg-2 col-md-3 col-sm-3 col-xs-12 tutr-prfile">
            <div class="profile-listing">
                <a href="<?php echo URL_HOME_TUTOR_PROFILE.'/'.$row->slug; ?>"><img src="<?php echo get_tutor_img($row->photo, $row->gender); ?>" alt="" class="img-responsive img-circle"></a>
            </div>

            <a href="<?php echo URL_HOME_TUTOR_PROFILE.'/'.$row->slug; ?>"><span class="tutor-name"><?php echo $row->username;?></span></a>
        </div>

       

        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div class="user-profile-content">
                <h4 class="title"><a href="<?php echo URL_HOME_VIEW_BLOG_DETAILS.'/'.$row->blog_id;?>"> <?php echo $row->title; ?></a></h4>
                <p><?php echo character_limiter($row->description, 300); ?> </p>
            </div>
        </div>

    </div>
</div>
<?php endforeach; else: ?>

<?php endif; ?>