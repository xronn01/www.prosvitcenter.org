<?php
/*
    Help and credits page - Maps Marker Pro
*/
//info prevent file from being accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) == 'leaflet-help-credits.php') { die ("Please do not access this file directly. Thanks!<br/><a href='https://www.mapsmarker.com/go'>www.mapsmarker.com</a>"); }
?>
<div class="wrap">
	<?php $lmm_options = get_option( 'leafletmapsmarker_options' ); ?>
	<?php include('inc' . DIRECTORY_SEPARATOR . 'admin-header.php'); ?>
	<p>
	<h3 style="font-size:23px;"><?php _e('Help','lmm') ?></h3>
	<p>
		<?php _e('Do you have questions or issues with Leaflet Maps Marker? Please use the following support channels appropriately.','lmm') ?>
	</p>
	<p>
		<strong><?php _e('Before you post a new support ticket, please follow the instructions from <a href="http://www.mapsmarker.com/readme-first" target="_blank">http://www.mapsmarker.com/readme-first</a> which give you a guideline on how to deal with the most common issues.','lmm') ?></strong>
	</p>
	<ul>
		<li>- <a href="https://www.mapsmarker.com/faq/" target="_blank"><?php _e('FAQ','lmm') ?></a>	<?php _e('(frequently asked questions)','lmm') ?></li>
		<li>- <a href="https://www.mapsmarker.com/docs/" target="_blank"><?php _e('Documentation','lmm') ?></a></li>
		<li>- <a href="https://www.mapsmarker.com/docs/changelog/" target="_blank"><?php _e('Changelog','lmm') ?></a></li>
		<li>- <a href="https://www.mapsmarker.com/login" target="_blank">mapsmarker.com/login</a> (<?php _e('support for pro users','lmm'); ?>)</li>
	</ul>
	<h3 style="font-size:23px;"><?php _e('Trademarks and copyright','lmm') ?></h3>
	<p>
	MapsMarker<sup style="font-size:75%;">&reg;</sup><br/>
	Copyright &copy; 2011-<?php echo date('Y'); ?>, MapsMarker.com e.U., All Rights Reserved
	</p>
	<h3 style="font-size:23px;"><?php _e('Translations','lmm') ?></h3>
	<p>
	<?php
	$translation_website = '<a href="https://translate.mapsmarker.com/projects/lmm" target="_blank">https://translate.mapsmarker.com/projects/lmm</a>';
	$translation_output = sprintf(__('Adding a new translation or updating an existing one is quite easy - please visit %s for more information!','lmm'),$translation_website);
	echo $translation_output;
	?>
	</p>
	<ul>
		<li>- Afrikaans (af) thanks to Hans, <a href="http://bmarksa.org/nuus/" target="_blank">http://bmarksa.org/nuus/</a></li>
		<li>- Arabic (ar) thanks to Abdelouali Benkheil, Aladdin Alhamda - <a href="http://bazarsy.com" target="_blank">http://bazarsy.com</a>, Nedal Elghamry - <a href="http://arabhosters.com" target="_blank">http://arabhosters.com</a>, yassin and Abdelouali Benkheil - <a href="http://www.benkh.be" target="_blank">http://www.benkh.be</a></li>
		<li>- Bengali (ba_BD) thanks to Nur Hasan - <a href="http://www.answersbd.com" target="_blank">http://www.answersbd.com</a></li>
		<li>- Bosnian (bs_BA) thanks to Kenan Dervišević - <a href="http://dkenan.com" target="_blank">http://dkenan.com</a></li>
		<li>- Bulgarian (bg_BG) thanks to Andon Ivanov - <a href="http://coffebreak.info" target="_blank">http://coffebreak.info</a></li>
		<li>- Catalan (ca) thanks to Vicent Cubells - <a href="http://vcubells.net" target="_blank">http://vcubells.net</a> and Efraim Bayarri - <a href="http://replicantsfactory.com" target="_blank">http://replicantsfactory.com</a></li>
		<li>- Chinese (zh_CN) thanks to John Shen - <a href="http://www.synyan.net" target="_blank">http://www.synyan.net</a> and ck</li>
		<li>- Chinese (zh_TW) thanks to jamesho Ho - <a href="http://outdooraccident.org" target="_blank">http://outdooraccident.org</a></li>
		<li>- Croatian (hr) thanks to Neven Pausic - <a href="http://www.airsoft-hrvatska.com" target="_blank">http://www.airsoft-hrvatska.com</a>, Alan Benic and Marijan Rajic - <a href="http://www.proprint.hr" target="_blank">http://www.proprint.hr</a></li>
		<li>- Czech (cs_CZ) thanks to Viktor Kleiner and Vlad Kuzba - <a href="http://kuzbici.eu" target="_blank">http://kuzbici.eu</a></li>
		<li>- Danish (da_DK) thanks to Mads Dyrmann Larsen and Peter Erfurt - <a href="http://24-7news.dk" target="_blank">http://24-7news.dk</a></li>
		<li>- Dutch (nl_NL) thanks to Marijke Metz - <a href="http://www.mergenmetz.nl" target="_blank">http://www.mergenmetz.nl</a> and Patrick Ruers - <a href="http://www.stationskwartiersittard.nl" target="_blank">http://www.stationskwartiersittard.nl</a></li>
		<li>- English (en_US)</li>
		<li>- Finnish (fi_FI) thanks to Jessi Bj&ouml;rk - <a href="https://twitter.com/jessibjork" target="_blank">@jessibjork</a></li>
		<li>- French (fr_FR) thanks to Vincèn Pujol - <a href="http://www.skivr.com" target="_blank">http://www.skivr.com</a> and Rodolphe Quiedeville - <a href="http://rodolphe.quiedeville.org" target="_blank">http://rodolphe.quiedeville.org</a>, Fx Benard - <a href="http://wp-translator.com" target="_blank">http://wp-translator.com</a>, cazal cédric - <a href="http://www.cedric-cazal.com" target="_blank">http://www.cedric-cazal.com</a>, Fabian Hurelle - <a href="http://hurelle.fr" target="_blank">http://hurelle.fr</a> and Thomas Guignard - <a href="http://news.timtom.ch" target="_blank">http://news.timtom.ch</a></li>
		<li>- Galician (gl_ES) thanks to Fernando Coello - <a href="http://www.indicepublicidad.com" target="_blank">http://www.indicepublicidad.com</a></li>
		<li>- German (de_DE)</li>
		<li>- Greek (el) thanks to Philios Sazeides - <a href="http://www.mapdow.com" target="_blank">http://www.mapdow.com</a>, Evangelos Athanasiadis - <a href="http://www.wpmania.gr" target="_blank">http://www.wpmania.gr</a> and Vardis Vavoulakis - <a href="http://avakon.com" target="_blank">http://avakon.com</a></li>
		<li>- Hebrew (he_IL) thanks to Alon Gilad - <a href="http://pluto2go.co.il" target="_blank">http://pluto2go.co.il</a> and kobi levi</li>
		<li>- Hindi (hi_IN) thanks to Outshine Solutions - <a href="http://outshinesolutions.com" target="_blank">http://outshinesolutions.com</a> and Guntupalli Karunakar - <a href="http://indlinux.org" target="_blank">http://indlinux.org</a></li>
		<li>- Hungarian (hu_HU) thanks to István Pintér - <a href="http://www.logicit.hu" target="_blank">http://www.logicit.hu</a> and Csaba Orban - <a href="http://www.foto-dvd.hu" target="_blank">http://www.foto-dvd.hu</a></li>
		<li>- Indonesian (id_ID) thanks to Andy Aditya Sastrawikarta and Emir Hartato - <a href="http://whateverisaid.wordpress.com" target="_blank">http://whateverisaid.wordpress.com</a> and Phibu Reza - <a href="http://www.dedoho.pw/" target="_blank">http://www.dedoho.pw/</a></li>
		<li>- Italian (it_IT) thanks to <a href="mailto:lucabarbetti@gmail.com">Luca Barbetti</a></li>
		<li>- Japanese (ja) thanks to Shu Higashi - <a href="http://twitter.com/higa4" target="_blank">@higa4</a></li>
		<li>- Korean (ko_KR) thanks to Andy Park - <a href="http://wcpadventure.com" target="_blank">http://wcpadventure.com</a></li>
		<li>- Latvian (lv) thanks to Juris Orlovs - <a href="http://lbpa.lv" target="_blank">http://lbpa.lv</a> and Eriks Remess - <a href="http://geekli.st/Eriks" target="_blank">http://geekli.st/Eriks</a></li>
		<li>- Lithuanian (lt_LT) thanks to Donatas Liaudaitis - <a href="http://www.transleta.co.uk" target="_blank">http://www.transleta.co.uk</a></li>
		<li>- Norwegian/Bokmål (nb_NO) translation thanks to Inge Tang - <a href="http://ingetang.com" target="_blank">http://ingetang.com</a></li>
		<li>- Polish (pl_PL) thanks to Pawel Wyszy&#324;ski - <a href="http://injit.pl" target="_blank">http://injit.pl</a>, Tomasz Rudnicki - <a href="http://www.kochambieszczady.pl" target="_blank"></a> and Robert Pawlak</li>
		<li>- Portuguese (pt_BR) thanks to Andre Santos - <a href="http://pelaeuropa.com.br" target="_blank">http://pelaeuropa.com.br</a> and Antonio Hammerl</li>
		<li>- Portuguese (pt_PT) thanks to Joao Campos - <a href="http://www.all-about-portugal.com" target="_blank">http://www.all-about-portugal.com</a></li>
		<li>- Romanian (ro_RO) thanks to Arian - <a href="http://administrare-cantine.ro" target="_blank">http://administrare-cantine.ro</a>, Daniel Codrea - <a href="http://www.inadcod.com" target="_blank">http://www.inadcod.com</a> and Flo Bejgu - <a href="http://www.inboxtranslation.com" target="_blank">http://www.inboxtranslation.com</a></li>
		<li>- Russian (ru_RU) thanks to Ekaterina Golubina - (supported by Teplitsa of Social Technologies - <a href="http://te-st.ru" target="_blank">http://te-st.ru</a>) and Vyacheslav Strenadko - <a href="http://poi-gorod.ru" target="_blank">http://poi-gorod.ru</a></li>
		<li>- Slovak (sk_SK) thanks to Zdenko Podobny</a></li>
		<li>- Slovenian (sl_SL) thanks to Anna Dukan - <a href="http://www.unisci24.com/blog/" target="_blank">http://www.unisci24.com/blog/</a></li>
		<li>- Swedish (sv_SE) thanks to Olof Odier - <a href="http://www.historiskastadsvandringar.se" target="_blank">http://www.historiskastadsvandringar.se</a>, Tedy Warsitha - <a href="http://codeorig.in/" target="_blank">http://codeorig.in/</a>, Dan Paulsson - <a href="http://www.paulsson.eu" target="_blank">http://www.paulsson.eu</a>, Elger Lindgren - <a href="http://20x.se" target="_blank">http://20x.se</a> and Anton Andreasson - <a href="http://andreasson.org/" target="_blank">http://andreasson.org/</a></li>
		<li>- Spanish (es_ES) thanks to David Ramírez - <a href="http://www.hiperterminal.com" target="_blank">http://www.hiperterminal.com</a>, Alvaro Lara - <a href="http://www.alvarolara.com" target="_blank">http://www.alvarolara.com</a>, Ricardo Viteri - <a href="http://www.labviteri.com" target="_blank">http://www.labviteri.com</a> and Juan Valdes</li>
		<li>- Spanish/Mexico (es_MX) thanks to Victor Guevera - <a href="http://1sistemas.net" target="_blank">http://1sistemas.net</a> and Eze Lazcano</li>
		<li>- Swedish (sv_SE) thanks to Olof Odier - <a href="http://www.historiskastadsvandringar.se" target="_blank">http://www.historiskastadsvandringar.se</a>, Tedy Warsitha - <a href="http://codeorig.in/" target="_blank">http://codeorig.in/</a> and Dan Paulsson - <a href="http://www.paulsson.eu" target="_blank">http://www.paulsson.eu</a></li>
		<li>- Thai (th) thanks to Makarapong Chathamma and Panupong Siriwichayakul - <a href="http://siteprogroup.com/" target="_blank">http://siteprogroup.com/</a></li>
		<li>- Turkish (tr_TR) thanks to Emre Erkan - <a href="http://www.karalamalar.net" target="_blank">http://www.karalamalar.net</a> and Mahir Tosun - <a href="http://www.bozukpusula.com" target="_blank">http://www.bozukpusula.com</a></li>
		<li>- Uighur (ug) thanks to Yidayet Begzad - <a href="http://ug.wordpress.org/" target="_blank">http://ug.wordpress.org/</a></li>
		<li>- Ukrainian (uk_UK) thanks to Andrexj - <a href="http://all3d.com.ua" target="_blank">http://all3d.com.ua</a> and Sergey Zhitnitsky - <a href="http://zhitya.com" target="_blank">http://zhitya.com</a></li>
		<li>- Vietnamese (vi) translation thanks to Hoai Thu - <a href="http://bizover.net" target="_blank">http://bizover.net</a></li>
		<li>- Yiddish (yi) thanks to Raphael Finkel - <a href="http://www.cs.uky.edu/~raphael/yiddish.html" target="_blank">http://www.cs.uky.edu/~raphael/yiddish.html</a></li>
	</ul>
	<h3 style="font-size:23px;"><?php _e('Licenses for used libraries, services and images','lmm') ?></h3>
	<ul>
		<li>- Leaflet by Cloudmade, <a href="http://www.leafletjs.com" target="_blank">http://www.leafletjs.com</a>, Copyright (c) 2010-<?php echo date('Y'); ?>, CloudMade, Vladimir Agafonkin</li>
		<li>- MiniMap plugin based on <a href="https://github.com/Norkart/Leaflet-MiniMap" target="_blank">https://github.com/Norkart/Leaflet-MiniMap</a> by Norkart (<a href="https://github.com/Norkart/Leaflet-MiniMap/blob/master/LICENSE.txt" target="_blank">License</a>)</li>
		<li>- Fullscreen plugin based on <a href="https://github.com/mapbox/Leaflet.fullscreen" target="_blank">https://github.com/mapbox/Leaflet.fullscreen</a> by mapbox (<a href="https://github.com/mapbox/Leaflet.fullscreen/blob/master/LICENSE" target="_blank">License</a>)</li>
		<li>- Google Maps and bing maps plugin by shramov - <a href="https://github.com/shramov/leaflet-plugins" target="_blank">https://github.com/shramov/leaflet-plugins</a></li>
		<li>- Leaflet.markercluster &copy; 2012-2015, Dave Leaver, smartrak - <a href="https://github.com/Leaflet/Leaflet.markercluster" target="_blank">https://github.com/Leaflet/Leaflet.markercluster</a></li>
		<li>- GPX plugin, &copy; 2011-2012 Pavel Shramov, &copy; 2013 Maxime Petazzoni, <a href="https://github.com/mpetazzoni/leaflet-gpx" target="_blank">https://github.com/mpetazzoni/leaflet-gpx</a>, <a href="https://github.com/mpetazzoni/leaflet-gpx/blob/master/LICENSE" target="_blank">License</a></li>
		<li>- Leaflet Locate plugin, &copy; 2013 Dominik Moritz, <a href="https://github.com/domoritz/leaflet-locatecontrol" target="_blank">https://github.com/domoritz/leaflet-locatecontrol</a>, <a href="https://github.com/domoritz/leaflet-locatecontrol/blob/gh-pages/LICENSE" target="_blank">License</a></li>
		<li>- OpenStreetMap: <a href="http://wiki.openstreetmap.org/wiki/OpenStreetMap_License" target="_blank">OpenStreetMap License</a></li>
		<li>- Datasource OGD Vienna maps: Stadt Wien - <a href="http://data.wien.gv.at" target="_blank">http://data.wien.gv.at</a></li>
		<li>- Address autocompletion powered by <a href="https://developers.google.com/places/documentation/autocomplete" target="_blank">Google Places API</a></li>
		<li>- GPX leaflet plugin copyright (C) 2011-2012 Pavel Shramov, copyright (C) 2013 Maxime Petazzoni, <a href="https://github.com/mpetazzoni/leaflet-gpx/blob/master/LICENSE" target="_blank">license</a></li>
		<li>- Jquery TimePicker by Trent Richardson, <a href="http://trentrichardson.com/examples/timepicker/" target="_blank">http://trentrichardson.com/examples/timepicker/</a>, license: GPL</li>
		<li>- <a href="https://mapicons.mapsmarker.com" target="_blank">Map Icons Collection</a> by Nicolas Mollet</li>
		<li>- Map center icon by <a href="http://glyphish.com/" target="_blank">Joseph Wain</a>, license: Creative Commons Attribution (by)</li>
		<li>- Question Mark Icon by <a href="http://www.randomjabber.com/" target="_blank">RandomJabber</a></li>
		<li>- Images for changelog from <a href="http://www.mozilla.org/en-US/firefox/11.0/releasenotes/">Firefox release notes</a>, license: Creative Commons Attribution ShareAlike (CC BY-SA 3.0)</li>
		<li>- Plus-, json-, layer-, language-, exclamation- &amp; csv-export-icon from <a href="http://p.yusukekamiyamane.com/" target="_blank">Yusuke Kamiyamane</a>, license: Creative Commons Attribution (by)</li>
		<li>- Home-Icon from <a href="http://prothemedesign.com/" target="_blank">Pro Theme Design</a>, license: Creative Commons Attribution (by)</li>
		<li>- Editor-Switch-Icon by AMAZIGH Aneglus, license: GNU/GPL</li>
		<li>- Submenu icons from <a href="http://bijou.im/" target="_blank">Bijou</a> and <a href="http://somerandomdude.com/work/iconic/" target="_blank">Iconic</a> icon sets (GPL)</li>
		<li>- Personalized avatar icon by <a href="http://semlabs.co.uk/" target="_blank">David Hopkins</a>, license: Creative Commons Attribution (by)</li>
		<li>- Anonymous avatar icon by <a href="http://www.iconshock.com/" target="_blank">Icon Shock</a>, license: Creative Commons Attribution (by)</li>
		<li>- Import/Export icons by <a href="http://momentumdesignlab.com/" target="_blank">Momenticons</a>, license: Creative Commons Attribution (by)</li>
		<li>- PHPExcel library for import/export <a href="http://phpexcel.codeplex.com/" target="_blank">http://phpexcel.codeplex.com/</a>, License: LGPL</li>
	</ul>
	<h3 style="font-size:23px;"><?php _e('Credits & special thanks','lmm') ?></h3>
	<ul>
		<li>- <a href="http://psha.org.ru/b/leaflet-plugins.html" target="_blank">shramov</a> for bing and google maps plugins for leaflet</li>
		<li>- Sindre Wimberger (<a href="http://www.sindre.at" target="_blank">http://www.sindre.at</a>) - bugfixing &amp; geo-consulting</li>
		<li>- <a href="http://alisothegeek.com/2011/01/wordpress-settings-api-tutorial-1/" target="_blank">WordPress-Settings-API-Class</a> by Aliso the geek</li>
		<li>- Hind who originally released a basic Leaflet plugin (not available anymore) which I used partly as a basis for Leaflet Maps Marker</li>
	</ul>
	</p>
</div>
<?php include('inc' . DIRECTORY_SEPARATOR . 'admin-footer.php'); ?>