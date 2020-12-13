<div class="col-md-10 padding-0">
   <div class="brade">
      <a href="<?php echo site_url();?>/student" style="text-decoration:none;"><?php echo $this->lang->line('home');?></a> 
      <?php if(isset($title))  echo " > "  .$this->lang->line('reports'). " > "  .$title;?>
   </div>
</div>
<div class="col-lg-10 col-md-10 col-sm-12 padding-lr">
   <div class="body-content">
   
   <div class="mud">
   	
   	<h3> <?php echo $this->lang->line('enter_your_payback_code');?></h3> 
     <?php echo $this->session->flashdata('message');?>      <?php echo form_open('virtual_class/payback', 'id="payback" '); ?> 
 <input type="text" name="payback_code" id="payback_code" />
  <input type="submit" class="pack" name="Submit" value="Submit" />
   </div>
   
 
<?php echo form_close();?> 
 </div>
 
</div>