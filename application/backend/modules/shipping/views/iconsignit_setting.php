<!DOCTYPE html>
<html lang="en">
<head>
    <title>IconsignitShipping</title>
    <!-- HTML5 Shim and Respond.js IE9 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Phoenixcoded">
    <meta name="keywords" content=", Flat ui, Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="Phoenixcoded">
    <!-- Favicon icon -->
    <link rel="icon" href="<?php echo base_url();?>resources/flatable/assets/images/favicon.ico" type="image/x-icon">
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- themify-icons line icon -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/assets/icon/themify-icons/themify-icons.css">
    <!-- ico font -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/assets/icon/icofont/css/icofont.css">
    <!-- flag icon framework css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/assets/pages/flag-icon/flag-icon.min.css">
    <!-- Menu-Search css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/assets/pages/menu-search/css/component.css">
    <!-- Switch component css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/bower_components/switchery/dist/switchery.min.css">
    <!-- Tags css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.css" />
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/assets/css/style.css">
    <!--color css-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>resources/flatable/assets/css/color/color-1.css" id="color"/>
</head>

<body class="horizontal-static">
<!-- Pre-loader start -->
<div class="theme-loader">
    <div class="ball-scale">
        <div></div>
    </div>
</div>
<!-- Pre-loader end -->
<!-- Main-body start -->
<div class="main-body">
    <div class="page-wrapper">
        <!-- Page header start -->
        <div class="page-header text-center">
            <div class="page-header-title">
                <h4>Iconsignit Shipping</h4>
                <span><?php echo $store_info['domain'];?></span>
            </div>
        </div>
        <!-- Page header end -->
        <!-- Page body start -->
        <div class="page-body">
            <div class="row">
                <div class="offset-sm-3 col-sm-6">
                    <div class="card">
                        <div class="card-block">
                            <h4 class="sub-title text-center">Shipping service key setting page</h4>
                            <?php if(isset($info)) { ?>
                                <div class="alert alert-info">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <i class="icofont icofont-close-line-circled"></i>
                                    </button>
                                    <strong>Info!</strong> <?php echo $info;?>
                                </div>
                            <?php } ?>
                            <?php if(isset($errors) && !empty($errors)) { ?>
                                <?php foreach($errors as $error) {?>
                                    <div class="alert alert-danger">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <i class="icofont icofont-close-line-circled"></i>
                                        </button>
                                        <strong>Error!</strong> <?php echo $error;?>
                                    </div>
                                <?php }?>
                            <?php } ?>
                            <form action="<?php echo base_url();?>shipping/setting/save" method="post">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Api Url</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="ApiUrl" value="<?php echo $setting_info['ApiUrl'];?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Api Token</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="ApiToken" value="<?php echo $setting_info['ApiToken'];?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Iconsignit Url</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="ConnectIconsignUrl" value="<?php echo $setting_info['ConnectIconsignUrl'];?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <?php if($bInstalledCustomShipping) { ?>
                                        <div class="col-sm-6">
                                            <button class="btn btn-primary btn-block" id="btnUninstall">Uninstall</button>
                                        </div>
                                        <div class="col-sm-6">
                                            <button class="btn btn-primary btn-block">Save / Install</button>
                                        </div>
                                    <?php } else { ?>
                                        <button class="btn btn-primary btn-block">Save / Install</button>
                                    <?php } ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page body end -->
    </div>
</div>
<!-- Required Jquery -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/tether/dist/js/tether.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- jquery slimscroll js -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/jquery-slimscroll/jquery.slimscroll.js"></script>
<!-- modernizr js -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/modernizr/modernizr.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/modernizr/feature-detects/css-scrollbars.js"></script>
<!-- classie js -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/classie/classie.js"></script>
<!-- Switch component js -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/switchery/dist/switchery.min.js"></script>
<!-- Tags js -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
<!-- Max-length js -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/bootstrap-maxlength/src/bootstrap-maxlength.js"></script>
<!-- i18next.min.js -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/i18next/i18next.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/i18next-xhr-backend/i18nextXHRBackend.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/i18next-browser-languagedetector/i18nextBrowserLanguageDetector.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/bower_components/jquery-i18next/jquery-i18next.min.js"></script>
<!-- Custom js -->
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/assets/js/script.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resources/flatable/assets/pages/advance-elements/swithces.js"></script>
<?php if($bInstalledCustomShipping) {?>
    <script type="text/javascript">
		$(document).ready(function() {
			$('#btnUninstall').on('click', function(e) {
				e.preventDefault();
				location.replace('<?php echo base_url('shipping/setting/uninstall/'.$old_module_id)?>');
            });
		});
    </script>
<?php }?>
</body>
</html>
