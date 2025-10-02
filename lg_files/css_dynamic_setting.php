
<!--
Navigation Bar:#d6620e
mouseover color: # d6620e
Change all button colors to: d6620e
Please also change the gray color at the bottom to the same color as the green navigation bar.
tah_dev.localhost.com
-->

<style>

.top-search button:hover, .cart-buttons a, .cart-buttons a.checkout, .menu > li:hover .current, .menu > li.sfHover .current, .menu > li:hover, .menu > li.sfHover, li.dropdown ul li a:hover, #jPanelMenu-menu li a:hover, input[type="button"], input[type="submit"], a.button, a.button.color, a.button.dark:hover, a.button.gray:hover, .icon-box:hover span, .tp-leftarrow:hover, .tp-rightarrow:hover, .sb-navigation-left:hover, .sb-navigation-right:hover, .product-discount, .newsletter-btn, #categories li a:hover, #categories li a.active, .flexslider .flex-prev:hover, .flexslider .flex-next:hover, .rsDefault .rsArrowIcn:hover, .hover-icon, #backtotop a:hover, #filters a:hover, #filters a.selected

{
	    background-color: <?php  echo $client_detail['mouse_over_button_color']; ?>;
}

a, .happy-clients-author, #categories li li a.active span, #categories li li a.active, #additional-menu ul li a:hover, #additional-menu ul li a:hover span, .mega a:hover, .mega ul li p a, #not-found i, .dropcap, .list-1.color li:before, .list-2.color li:before, .list-3.color li:before, .list-4.color li:before, .comment-by span.reply a:hover, .comment-by span.reply a:hover i, #categories li ul li a:hover span, #categories li ul li a:hover, table .cart-title a:hover, .st-val a:hover, .meta a:hover {
    color: <?php  echo $client_detail['mouse_over_button_color']; ?>;
}

/*a:hover {
    color: <?php  echo $client_detail['nav_color']; ?>;
}*/

.checkout-section {
     background: <?php  echo $client_detail['nav_color']; ?> !important;
	 color:#fff;
	
	}
	
.checkout-section span {
 color:#fff;
}	

.checkout-section strong {
	color:#fff;
}
	
	
.checkout-section.active {
    background: <?php  echo $client_detail['nav_color']; ?> !important;
    color: #fff;
}

 

.checkout-section.cart {
    padding: 12px 23px 14px 23px;
    background: <?php  echo $client_detail['nav_color']; ?>;
    color: #fff;
    margin: 0;
}

#jPanelMenu-menu a.current {
    background: <?php  echo $client_detail['nav_color']; ?> !important;
}


 @media only screen and (max-width: 767px)
a.menu-trigger {
  
    background: blue !important;
	
	}
	
	.top-bar-dropdown ul li a:hover, .skill-bar-value, .counter-box.colored, a.menu-trigger:hover, .pagination .current, .pagination ul li a:hover, .pagination-next-prev ul li a:hover, .tabs-nav li.active a, .dropcap.full, .highlight.color, .ui-accordion .ui-accordion-header-active:hover, .ui-accordion .ui-accordion-header-active, .trigger.active a, .trigger.active a:hover, .share-buttons ul li:first-child a, a.caption-btn:hover, .mfp-close:hover, .mfp-arrow:hover, .img-caption:hover figcaption, #price-range .ui-state-default, .selectricItems li:hover, .product-categories .img-caption:hover figcaption, .rsDefault .rsThumbsArrow:hover, .customSelect .selectList dd.hovered, .qtyplus:hover, .qtyminus:hover, a.calculate-shipping:hover, .og-close:hover, .tags a:hover {
    background: <?php  echo $client_detail['nav_color']; ?> !important;
}

.qtyplus, .qtyminus, a.cart-remove {
    background:<?php  echo $client_detail['mouse_over_button_color']; ?> !important;
	color:white;
	
	}

<?php
$css_checkout_header_link = "";
if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
{
?>

#checkout_header_link {
    display:block;
}

<?php
}else{
?>

#checkout_header_link {
    display:none;;
}


<?php
}
?>

.cart-btn a.button{
background-color:<?php  echo $client_detail['mouse_over_button_color']; ?> !important;
}

/*#cart a {
background-color:<?php  echo $client_detail['nav_color']; ?> !important;
}
*/
.inactive_btn{
    background-color: <?php  echo $client_detail['nav_color']; ?> !important;
}


.menu {
    position: relative;
    padding: 0;
    list-style: none;
    float: left;
    width: 100%;
    max-height: 160px;
    background-color: <?php  echo $client_detail['nav_color']; ?> !important;;
    margin: 15px 0 25px 0;
    z-index: 99;
}

.menu > li .current {
    background-color: <?php  echo $client_detail['nav_color']; ?>;
}
 

</style>
