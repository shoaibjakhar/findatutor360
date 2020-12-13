<!-- Dashboard panel ends -->
<?php echo $message;?>
<div class="dashboard-panel">

<div class="col-md-10 padding-0">
   <div class="brade">
      <a href="<?php echo site_url();?>/student" style="text-decoration:none;"><?php echo get_languageword('home');?></a> 
      <?php if(isset($title))  echo " > "  . $title;?>
   </div>
</div>
<div class="col-lg-10 col-md-10 col-sm-12 padding-lr">
   <div class="body-content">
     <div class="col-md-12">
      
      <div class="meet">
      <h1> <?php echo ucwords($meeting_name);?> <br /> <?php echo get_languageword('with_meeting_id');?> <?php echo $meeting_id;?></h1>
<a class="pack" href="<?php echo $url;?>" target="_blank"> <i class="fa  fa-user-plus"></i> <?php echo get_languageword('join_now');?></a>

      </div>      
      
      </div>
      </div>
</div>



</div>