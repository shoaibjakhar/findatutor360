<link rel="stylesheet" href="<?php echo URL_FRONT_CSS;?>blog.css"> 

    <!-- Page Title Wrap  -->
    <div class="page-title-wrap">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                </div>
            </div>
        </div>
    </div>
    <!-- Page Title Wrap  -->

    <!-- News Scroller  -->
    <?php $this->load->view('template/site/scroller'); ?>
    <!-- News Scroller  -->

    <!-- Dashboard Section  -->
    <section class="dashboard-section">
        <div class="container">

            
            <div class="row offcanvas offcanvas-right row-margin">

              

                <div class="col-xs-12 col-sm-12 dashboard-content ">
                    <!-- Breadcrumb -->
                    <ol class="breadcrumb dashcrumb">
                        <li><a href="<?php echo SITEURL;?>"><?php echo get_languageword("home");?></a></li>
                        <li class="active"><?php if(isset($pagetitle)) echo $pagetitle;?></li>
                    </ol>
                    <!-- Breadcrumb ends -->

                   
                    
                   <div class="margin-btm">
                     <?php 
                        echo form_open(URL_HOME_LIST_BLOGS, 'id="filter_form"'); ?> 

                        <div class="row">
                            <div class="col-md-12">

                            <div class="col-md-4">
                               <?php
                               if (!empty($tutors_options)) {  

                                    $sel = set_value('tutors', (!empty($tutors)) ? $tutors : '');
                                    echo form_multiselect('tutors[]', $tutors_options, $sel, 'class="select-picker" id="tutors" onchange="get_filter_result();"');
                                
                                } ?>
                            </div>
                            
                            </div>
                        </div>
                        <?php  echo form_close();?>
                    </div>
                   


                    <!-- Dashboard Panel -->
                    <div class="dashboard-panel">
                      
                            <div id="blogs_list">

                                <?php 
                                      if(!empty($blogs)): 
                                        $this->load->view('sections/list_blogs', array('blogs' => $blogs), false);
                                      else:
                                ?>
                                 <p>Blog(s) not available.</p>
                                <?php endif; ?>

                            </div>

                            <?php if($total_records > LIMIT_BLOG_LIST) { ?>
                            <div class="row" id="div_load_more">
                                <div class="col-sm-12 text-center">
                                    <div class="load-more" onclick="load_more();">
                                        <input type="hidden" name="limit" id="limit" value="<?php echo LIMIT_BLOG_LIST;?>" />
                                        <input type="hidden" name="offset" id="offset" value="<?php echo LIMIT_BLOG_LIST;?>" />
                                        <a class="btn-link" id="btn_load_more"> <?php echo get_languageword("load_more");?></a>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>

                    </div>


                    <!-- Ends Dashboard Panel -->
                </div>
            </div>
        </div>
    </section>
    <!-- Ends Dashboard section  -->


<script src="<?php echo URL_FRONT_JS;?>jquery.js"></script>
<script>

$(function() {

    $('option[value=""]').attr('disabled', true);
    $('option[value=""]').prop('selected', false);
});


function get_filter_result()
{
    document.getElementById('filter_form').submit();
}


function load_more()
{
    $.ajax({
        url:"<?php echo URL_HOME_LOAD_MORE_BLOGS; ?>",
        data:{
          offset        : $('#offset').val(),
          limit         : $('#limit').val(),
          tutors        : $('#tutors').val()
        },
        type:"post", 
        beforeSend: function() {
            $('#btn_load_more').html('<i class="fa fa-spinner"></i> <?php echo get_languageword("loading");?>');
        },
        success :function(data){

            $('#btn_load_more').html(' <?php echo get_languageword("load_more");?>');

            dta = $.parseJSON(data);

            if(dta.result == "\r\n" || dta.result == "\n") {

                $('#div_load_more').html('<?php echo get_languageword("You have reached end of the list.");?>');

            } else {


                $(dta.result).hide().appendTo("#blogs_list").fadeIn(1000);
                $('#offset').val(dta.offset);
                $('#limit').val(dta.limit);
            }
        }
    })
}
</script>

