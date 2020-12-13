<html>
  <head>
    <title>Course Completion Certificate</title>
	<link href="<?php echo URL_FRONT_CSS; ?>bootstrap.min.css" rel="stylesheet">
  </head>
  <body>

    <div class="container">
	<div class="row">
	<div class="col-md-8 col-sm-8 col-md-offset-2 col-sm-offset-2 col-xs-12" style="border-radius:5px;margin-top:50px;border:5px solid #e6e7ea;padding: 30px 70px;background-size:cover;position:relative;">
	 <div class="row">
	  <div class="col-md-12 col-sm-12 col-xs-12">
	   <h1 class="text-center" style="color:#14bdee;"><?php echo get_languageword('Certificate of Completion'); ?></h1>
	   <h3 class="text-center"><?php echo get_languageword('Tutors Menorah'); ?></h3>
	   <h4 class="text-center"><?php echo get_languageword('This Document Certifies that'); ?></h4>
	  </div>
	  
	  <div class="col-md-12 col-sm-12 col-xs-12">
	    <h2 class="text-center" style="color:#524628;"><?php if(!empty($user_rec->username)) echo $user_rec->username; ?></h2>
	  </div>
	  
	  
	  <div class="col-md-12 col-sm-12 col-xs-12">
	   <h4 class="text-center">Have Completed a course entitled <?php if(!empty($booking_det->course_name)) echo '"'.$booking_det->course_name.'"'; ?> <?php echo get_languageword('at Tutors Menorah'); ?></h4>
	  </div>
	  
	  <div class="col-md-12 col-sm-12 col-xs-12">
	   <h3 class="text-center"><?php echo get_languageword('During'); ?> <?php echo date('d-m-Y', strtotime($booking_det->start_date)); ?> <?php echo get_languageword('To'); ?> <?php echo date('d-m-Y', strtotime($booking_det->end_date)); ?></h3>
	  </div>
	  
	  <div class="row">
	   <div class="col-md-4 col-sm-4 col-xs-4">
	    <h4 class="text-left"><?php echo get_languageword('Signature'); ?>: <?php if(!empty($tutor_rec->signature)) echo $tutor_rec->signature; ?></h4>
	   </div>
	   
	   <div class="col-md-4 col-sm-4 col-xs-4">
	     <h4 class="text-center"><?php echo get_languageword('Date'); ?>: <?php if(!empty($booking_det->certificate_issue_date)) echo $booking_det->certificate_issue_date; ?></h4>
	   </div>
	   
	   <div class="col-md-4 col-sm-4 col-xs-4 clearfix">
	     <img src="<?php echo URL_PUBLIC_UPLOADS_SETTINGS;?>setting_35.png" alt="tutor" class="img-responsive pull-right" style="background:#736969;">
	   </div>
	  </div>
	 </div>
	 </div>
	</div>
	
  </body>

</html>