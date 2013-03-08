/**
 * Add In Mage::
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the EULA that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL: http://add-in-mage.com/support/presales/eula/
 *
 *
 * PROPRIETARY DATA
 * 
 * This file contains trade secret data which is the property of Add In Mage:: Ltd. 
 * This file is submitted to recipient in confidence.
 * Information and source code contained herein may not be used, copied, sold, distributed, 
 * sub-licensed, rented, leased or disclosed in whole or in part to anyone except as permitted by written
 * agreement signed by an officer of Add In Mage:: Ltd.
 * 
 * 
 * @category    AddInMage
 * @package     js
 * @copyright   Copyright (c) 2012 Add In Mage:: Ltd. (http://www.add-in-mage.com)
 * @license     http://add-in-mage.com/support/presales/eula/  End User License Agreement (EULA)
 * @author      Add In Mage:: Team <team@add-in-mage.com>
 */

function stwOpenView(view)
{
	$$('ul.services').invoke('hide');
	$$('#container div.service-specific').invoke('hide');
	$('service-box').addClassName('service-view');
	$(view + '-view').show();
	$$('#service-box div.stw-services').invoke('addClassName',
			'container-active');
	$('back').show();
	if ($('services-pagination')) $('services-pagination').hide();
}

function stwBackFrom()
{
	if ($('manual-view').visible() && $('services-pagination')) stwNextSP(1);
	if ($('adm-stw-top-manual-container')) {
		$('adm-stw-top-manual-container').hide();
		$('adm-stw-top-container').show();
	}
	$$('#block-description').invoke('show');
	$('service-box').removeClassName('service-view');
	$$('div.service-specific').invoke('hide');
	$$('ul.services').invoke('show');
	$('services').removeClassName('manual_view_active');
	$('container').removeClassName('manual-min-height');
	$$('.stw-services').invoke('removeClassName', 'container-active');
	$('back').hide();
	if ($('services-pagination')) $('services-pagination').show();
	$('second-step').removeClassName('active-step');
	$('first-step').addClassName('active-step');
}

function stwProcessData(form)
{
	var stwForm = new VarienForm(form, true);
}

function stwProcessManualView()
{
	$('adm-stw-top-manual-container').show();
	$('adm-stw-top-container').hide();
	$$('.stw-services').invoke('hide');
	$('services').addClassName('manual_view_active');
	$('manual-view').up().show();
	$('container').addClassName('manual-min-height');
	$('block-description').hide();
	$('second-step').addClassName('active-step');
	$('first-step').removeClassName('active-step');
}

function stwNextSP(index)
{
	$$('.service-page-active').invoke('removeClassName', 'service-page-active');
	$$("#service-box div.stw-services").invoke('hide');
	$("service-box").down().next('div', index - 1).show();
	if (index == 1) $("services-pagination").down().addClassName(
			'service-page-active');
	else
		$("services-pagination").down().next('span', index - 2).addClassName(
				'service-page-active');
}

function stwSelect(el)
{
	el.up('li').addClassName('adm-stw-selected');
	ul_selected.insert({
		top : new Element('LI').writeAttribute('name', el.readAttribute('id'))
				.update(el.up().next('.friend-data').down(1).innerHTML)
	});
	stwCountChecked();
}

function stwUnselect(el)
{
	el.up('li').removeClassName('adm-stw-selected');
	stwCountChecked();
}

function stwValidateUpl(service)
{
	var valid_file = true;

	if ($F(service + '-select') == "") {
		$(service + '-select').addClassName('validation-failed');
		var valid_file = false;
	} else
		$(service + '-select').removeClassName('validation-failed');

	if ($('sender_name_' + service) && $F('sender_name_' + service) == "") {
		$('sender_name_' + service).addClassName('validation-failed');
		var valid_file = false;
	} else {
		if ($('sender_name_' + service)) {
			$('sender_name_' + service).removeClassName('validation-failed');
		}
	}

	if ($('sender_email_' + service) && $F('sender_email_' + service) == "") {
		$('sender_email_' + service).addClassName('validation-failed');
		var valid_file = false;
	} else {
		if ($('sender_email_' + service)) {
			$('sender_email_' + service).removeClassName('validation-failed');
		}
	}

	if (valid_file) $(service).submit();
}

function stwCountCharsHelper(el, maxchars, limited)
{
	if ($F(el).length >= maxchars) {
		if (limited) {
			$(el).value = $F(el).substring(0, maxchars);
		}
		$('counter-' + el).removeClassName('adm-stw-chars-limit-ok');
	} else {
		$('counter-' + el).addClassName('adm-stw-chars-limit-ok');
	}
	$('counter-' + el).update(maxchars - $F(el).length + '/' + maxchars);
}

function stwCountChars(el, maxchars, limited)
{
	if (limited == null) limited = true;
	if ($(el)) {
		Event.observe($(el), 'keyup', function()
		{
			stwCountCharsHelper(el, maxchars, limited);
		}, false);
		Event.observe($(el), 'keydown', function()
		{
			stwCountCharsHelper(el, maxchars, limited);
		}, false);
		stwCountCharsHelper(el, maxchars, limited);
	}
}

function stwShowLetter(letter)
{
	$('adm-stw-letter').update(letter).show();
}

var stw_timer = 3;
var stw_slide_speed = 70;
var stw_scroll_timer = 70;
var stw_scroll_speed = 70;
var stw_opacity = 40;

function stwScrollFriends(id, prefix, timer, el)
{
	var div = $(id);
	var slider = div.parentNode;
	$$('.adm-stw-letters').invoke('removeClassName','current-letter');
	el.addClassName('current-letter');
	clearInterval(slider.timer);
	slider.section = parseInt(id.replace(/\D/g, ''));	
	slider.target = div.offsetTop;	
	slider.style.top = slider.style.top || '0px';
	//slider1.setValue();
	slider.current = slider.style.top.replace('px', '');
	slider.direction = (Math.abs(slider.current) > slider.target) ? 1 : -1;
	slider.style.opacity = stw_opacity * .01;
	slider.style.filter = 'alpha(opacity=' + stw_opacity + ')';
	slider.timer = setInterval(function()
	{
		stwSlideAnimate(slider, prefix, timer);
	}, stw_timer);
}

function stwSlideAnimate(slider, prefix, timer)
{
	var curr = Math.abs(slider.current);
	var tar = Math.abs(slider.target);
	var dir = slider.direction;
	$('friends').scrollTop = 0;
	if ((tar - curr <= stw_slide_speed && dir == -1)
			|| (curr - tar <= stw_slide_speed && dir == 1))
	{
		slider.style.top = (slider.target * -1) + 10 + 'px';
		slider.style.opacity = 1;
		slider.style.filter = 'alpha(opacity=100)';
		clearInterval(slider.timer);
		if (slider.autoscroll) setTimeout(function()
		{
			autoScroll(slider.id, prefix, timer);
		}, timer * 1000);
	} else {
		var pos = (dir == 1) ? parseInt(slider.current) + stw_slide_speed
				: slider.current - stw_slide_speed;
		slider.current = pos;
		slider.style.top = pos + 'px';
	}
}

function stwScrollVertical(value, element, slider) 
{
	element.scrollTop = Math.round(value/slider.maximum*(element.scrollHeight-element.offsetHeight));
} 

function stwCheckControlElm(scrll,slider,track)
{
	if ($(scrll).scrollHeight <= $(scrll).offsetHeight) {
		slider.setDisabled();
		$(track).hide();
	} 
}

function stwCheckControlElms()
{
	if ($('friends').scrollHeight <= $('friends').offsetHeight) { slider1.setDisabled(); $('track1').hide(); } else { slider1.setEnabled(); $('track1').show(); }
	if ($('adm-stw-selected-friends').scrollHeight <= $('adm-stw-selected-friends').offsetHeight) {	slider2.setDisabled(); $('track2').hide(); } else { slider2.setEnabled(); $('track2').show(); }
}