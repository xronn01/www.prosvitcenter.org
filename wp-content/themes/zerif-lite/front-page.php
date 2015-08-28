<?php get_header(); ?>


<?php
 //Dlya vidobragenya Slayda na golovniy storintsi
       wowslider(12);
if ( get_option( 'show_on_front' ) == 'page' ) {
    ?>
	<div class="clear"></div>

	</header> <!-- / END HOME SECTION  -->



	<!--	<div id="content" class="site-content"> -->

 <div class="container">



	<div class="content-left-wrap col-md-9">



		<div id="primary" class="content-area">

			<main id="main" class="site-main" role="main">



			<?php if ( have_posts() ) : ?>



				<?php /* Start the Loop */ ?>

				<?php while ( have_posts() ) : the_post(); ?>



					<?php

						/* Include the Post-Format-specific template for the content.

						 * If you want to override this in a child theme, then include a file

						 * called content-___.php (where ___ is the Post Format name) and that will be used instead.

						 */

						get_template_part( 'content', get_post_format() );

					?>



				<?php endwhile; ?>



				<?php zerif_paging_nav(); ?>



			<?php else : ?>



				<?php get_template_part( 'content', 'none' ); ?>



			<?php endif; ?>



			</main><!-- #main -->

		</div><!-- #primary -->



	</div><!-- .content-left-wrap -->



	<div class="sidebar-wrap col-md-3 content-left-wrap">

		<?php get_sidebar(); ?>

	</div><!-- .sidebar-wrap -->



	</div><!-- .container -->
	<?php
   }else {

	if(isset($_POST['submitted'])) :


			/* recaptcha */

			$zerif_contactus_sitekey = get_theme_mod('zerif_contactus_sitekey');

			$zerif_contactus_secretkey = get_theme_mod('zerif_contactus_secretkey');

			$zerif_contactus_recaptcha_show = get_theme_mod('zerif_contactus_recaptcha_show');

			if( isset($zerif_contactus_recaptcha_show) && $zerif_contactus_recaptcha_show != 1 && !empty($zerif_contactus_sitekey) && !empty($zerif_contactus_secretkey) ) :

		        $captcha;

		        if( isset($_POST['g-recaptcha-response']) ){

		          $captcha=$_POST['g-recaptcha-response'];

		        }

		        if( !$captcha ){

		          $hasError = true;

		        }

		        $response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=".$zerif_contactus_secretkey."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR'] );

		        if($response['body'].success==false) {

		        	$hasError = true;

		        }

	        endif;



			/* name */


			if(trim($_POST['myname']) === ''):


				$nameError = __('* Please enter your name.','zerif-lite');


				$hasError = true;


			else:


				$name = trim($_POST['myname']);


			endif;


			/* email */


			if(trim($_POST['myemail']) === ''):


				$emailError = __('* Please enter your email address.','zerif-lite');


				$hasError = true;


			elseif (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", trim($_POST['myemail']))) :


				$emailError = __('* You entered an invalid email address.','zerif-lite');


				$hasError = true;


			else:


				$email = trim($_POST['myemail']);


			endif;


			/* subject */


			if(trim($_POST['mysubject']) === ''):


				$subjectError = __('* Please enter a subject.','zerif-lite');


				$hasError = true;


			else:


				$subject = trim($_POST['mysubject']);


			endif;


			/* message */


			if(trim($_POST['mymessage']) === ''):


				$messageError = __('* Please enter a message.','zerif-lite');


				$hasError = true;


			else:


				$message = stripslashes(trim($_POST['mymessage']));


			endif;





			/* send the email */


			if(!isset($hasError)):


				$zerif_contactus_email = get_theme_mod('zerif_contactus_email');

				if( empty($zerif_contactus_email) ):

					$emailTo = get_theme_mod('zerif_email');

				else:

					$emailTo = $zerif_contactus_email;

				endif;


				if(isset($emailTo) && $emailTo != ""):

					if( empty($subject) ):
						$subject = 'From '.$name;
					endif;

					$body = "Name: $name \n\nEmail: $email \n\n Subject: $subject \n\n Message: $message";


					$headers = 'From: '.$name.' <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $email;


					wp_mail($emailTo, $subject, $body, $headers);


					$emailSent = true;


				else:


					$emailSent = false;


				endif;


			endif;


		endif;



	$zerif_bigtitle_show = get_theme_mod('zerif_bigtitle_show');

	if( isset($zerif_bigtitle_show) && $zerif_bigtitle_show != 1 ):

		include get_template_directory() . "/sections/big_title.php";
	endif;


?>


</header> <!-- / END HOME SECTION  -->


<div id="content" class="site-content">



<?php

	/* OUR FOCUS SECTION */

	$zerif_ourfocus_show = get_theme_mod('zerif_ourfocus_show');

	if( isset($zerif_ourfocus_show) && $zerif_ourfocus_show != 1 ):
		include get_template_directory() . "/sections/our_focus.php";
	endif;


	/* RIBBON WITH BOTTOM BUTTON */


	include get_template_directory() . "/sections/ribbon_with_bottom_button.php";




?>
<section class="our-team" id="team"><div class="container">
    <div class="section-header"><h2 class="dark-text">Партнери</h2></div>
        <div class="row" data-scrollreveal="enter left after 0s over 0.1s" data-sr-init="true" data-sr-complete="true">

            <?php echo do_shortcode('[wonderplugin_carousel id="6"]'); ?>

        </div></div></section>


	<!-- TESTIMONIALS -->

	<section class="testimonial">
	<div class="section-header"><h2 class="white-text">Підтримати</h2></div>
	<div class="container" data-scrollreveal="enter right after 0s over 1s" data-sr-init="true" data-sr-complete="true"><div class="col-md-12"><div id="client-feedbacks" class="owl-carousel owl-theme"><div class="widget zerif_testim">

        <p>Якщо ви поділяєте наші цінності і прагнете нам допомогти творити зміни в освіті вже сьогодні, ви можете підтримати нас фінансово. Будь-яка сума вітається. Якщо внесок перевищує 500 гривень, нашому донорові ми <br>надаємо звіт про використані кошти.</p>
        <h3>Всі зібрані гроші ідуть на:</h3>

    <?php echo do_shortcode("[su_row][su_column size='1/5']<div class='qwww' id ='qw1'></div><p>Стипендії для навчаннявчителів</p>[/su_column]
 [su_column size='1/5']<div class='qwww' id ='qw2'></div><p>Освітні зустрічі</p>[/su_column]
[su_column size='1/5']<div class='qwww' id ='qw3'></div><p>Навчальні тури і поїздки</p>[/su_column]
 [su_column size='1/5']<div class='qwww' id ='qw4'></div><p>Розробку курсів</p>[/su_column]
 [su_column size='1/5']<div class='qwww' id ='qw5'></div><p>Організацію днів батьків і дітей</p>[/su_column] [/su_row]")?>
 <a href="" class="buuut">Мій внесок в освіту<img src="http://www.prosvitcenter.org/wp-content/uploads/2015/08/strilka.fw_.png" class="hrimg"></a>




        </div>

</section>
<?



	/* RIBBON WITH RIGHT SIDE BUTTON */


	include get_template_directory() . "/sections/ribbon_with_right_button.php";

?>

	<!-- LATEST NEWS -->
<section class="latest-news" id="latestnews">

<div class="container">



<div class="section-header">



 <h2 class="dark-text"> НОВИНИ </h2>

</div><!-- END .section-header -->

<div class="clear"></div>

<div id="carousel-homepage-latestnews" class="carousel slide" data-ride="carousel">



<div class="carousel-inner" role="listbox">

<? echo do_shortcode("[su_posts template='templates/hom-loop.php' posts_per_page='10' tax_term='9' tax_operator='0' order='desc']");?>





</div><!-- .carousel-inner -->



</div><!-- #carousel-homepage-latestnews -->

</div><!-- .container -->
</section>




	<!-- LAyers -->
    <section class="latest-bla-bla">
        <div class="container">
            <!-- SECTION HEADER -->
            <div class="section-header">

                <h2>ВІДГУКИ</h2>
            </div>
            <div class="content">
            <?echo do_shortcode("
[su_column size='1/3']<img class='alignnone size-full wp-image-352' src='http://www.prosvitcenter.org/wp-content/uploads/2015/08/layer.3.fw_.png' alt='layer.3.fw' width='130' height='130' />
<h3>Юліана Петрова</h3>

посада<br>
<p style='text-align: justify;'>“15 листопада о 12:00 Центр інноваційної освіти Про.СВІТ та спільнота Nravo Kids запрошує на день сімейного дозвілля. Використайте чудову можливість розвиватись разом з усією сім’єю.”</p>
    [/su_column]

[su_column size='1/3']<img class='alignnone size-full wp-image-351' src='http://www.prosvitcenter.org/wp-content/uploads/2015/08/layer.2.fw_.png' alt='layer.2.fw' width='130' height='130' />

<h3>Олександр Кисіль</h3>

посада<br>
<p style='text-align: justify;'>“Дорогі батьки, ми оголошуємо додатковий набір дітей для навчання у “Про.Світ”. Кількість місць обмежена - лише 6, тому поспішіть зареєструвати свою дитину на курси.”</p>
    [/su_column]

[su_column size='1/3']<img class='alignnone size-full wp-image-353' src='http://www.prosvitcenter.org/wp-content/uploads/2015/08/layer.fw_.png' alt='layer.fw' width='130' height='130' />
<h3>Оля Коновалець</h3>

посада<br>
<p style='text-align: justify;'>“15 листопада о 12:00 Центр інноваційної освіти Про.СВІТ та спільнота Nravo Kids запрошує на день сімейного дозвілля. Використайте чудову можливість розвиватись разом з усією сім’єю.”[/su_column]</p>
    ")?>
            <div
            </div>
    </section>

		<section class="contact-us" id="contact">

				<!-- / END SECTION HEADER -->

				<!-- CONTACT FORM-->
				<div class="row">



					<form role="form" method="POST" action="" onSubmit="this.scrollPosition.value=(document.body.scrollTop || document.documentElement.scrollTop)" class="contact-form">

						<input type="hidden" name="scrollPosition">

						<input type="hidden" name="submitted" id="submitted" value="true" />

						<div class="col-lg-4 col-sm-4" data-scrollreveal="enter left after 0s over 1s">

							<input required="required" type="text" name="myname" placeholder="Your Name" class="form-control input-box" value="<?php if(isset($_POST['myname'])) echo esc_attr($_POST['myname']);?>">

						</div>

						<div class="col-lg-4 col-sm-4" data-scrollreveal="enter left after 0s over 1s">

							<input required="required" type="email" name="myemail" placeholder="Your Email" class="form-control input-box" value="<?php if(isset($_POST['myemail'])) echo is_email($_POST['myemail']) ? $_POST['myemail'] : ""; ?>">

						</div>





						<?php
							$zerif_contactus_button_label = get_theme_mod('zerif_contactus_button_label','Send Message');
							if( !empty($zerif_contactus_button_label) ):
								echo '<button class="btn btn-primary custom-button red-btn" type="submit" data-scrollreveal="enter left after 0s over 1s">'.$zerif_contactus_button_label.'</button>';
							endif;
						?>

						<?php

							$zerif_contactus_sitekey = get_theme_mod('zerif_contactus_sitekey');
							$zerif_contactus_secretkey = get_theme_mod('zerif_contactus_secretkey');
							$zerif_contactus_recaptcha_show = get_theme_mod('zerif_contactus_recaptcha_show');

							if( isset($zerif_contactus_recaptcha_show) && $zerif_contactus_recaptcha_show != 1 && !empty($zerif_contactus_sitekey) && !empty($zerif_contactus_secretkey) ) :

								echo '<div class="g-recaptcha" data-sitekey="' . $zerif_contactus_sitekey . '"></div>';

							endif;

						?>

					</form>

				</div>
</section> <!-- / END CONTACT US SECTION-->
				<!-- / END CONTACT FORM-->
<div class="enddd">
<h2>контакти</h2>
<p>
Якщо у Вас виникають запитання, пишіть нам на e-mail prosvit.center@gmail.com або телефонуйте за номером +38 050 370 99 66.<br>
Наш офіс розташований за адресою вул. Личаківська,7, Львів.</p>
</div>

			</div> <!-- / END CONTAINER -->

<?php }?>
<div class="beforethtbestfooteronthesite"><?php echo do_shortcode("[mapsmarker marker='1']"); ?></div>



<? get_footer(); ?>
