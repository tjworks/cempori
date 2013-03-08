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

function stwSendQueued()
{
	if (stwTestBrowser()) stwSendingAdv();
	else
		stwSendingOld();
}

function stwTestBrowser()
{
	var use_progress = false;
	if (/Safari[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
		var sfrversion = new Number(RegExp.$1);
		if (sfrversion >= 533) use_progress = true;
	} else if (/Chrome[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
		var chrversion = new Number(RegExp.$1);
		if (chrversion >= 12) use_progress = true;
	} else if (/Opera[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
		var oprversion = new Number(RegExp.$1);
		if (oprversion >= 10) use_progress = true;
	} else if (/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
		var ffversion = new Number(RegExp.$1);
		if (ffversion >= 6) use_progress = true;
	} else
		use_progress = false;
	return use_progress;
}

function stwOverlay()
{
	var loadmask = $('stw-adminhtml-wait');
	$('spreadtheword_queue_grid').insert(loadmask);
	loadmask.show();
}

function stwTimeout()
{
	$('stw-wait-container').addClassName('timeout-notice');
	$('stw-running-process').remove();
	$('stw-status-container').hide();
	$('stw-notice-container').show();
}

function stwNoJob()
{
	$('stw-running-process').remove();
	setTimeout(function()
	{
		$('stw-adminhtml-wait').remove();
	}, 5000);
}

function stwExeption()
{
	$('stw-running-process').remove();
}

function stwUpdate(data)
{
	$('stw-progress-bar').style.width = data.percent + '%';
	if (data.percent !== 0) $('stw-progress').update(
			Math.floor(data.percent) + '% complete');

	if (data.text) {
		if (data.text.status) {
			$('stw-response-data').show();
			$('stw-status').update(data.text.status);
		}
		if (data.text.error) {
			$('stw-response-failed').show();
			$('stw-failed-qty').update(data.text.failed);
			$('stw-reason').update(data.text.error);
		}
	}

	if (data.current) {
		$('stw-response-sending').show();
		$('stw-sending').update(data.current + ' / ' + data.max);
	}
	if (data.timeTaken) {
		$('stw-response-time').show();
		$('stw-elapsed').update(_stwSecondsToHMS(data.timeTaken));
		if (data.timeRemaining || data.timeRemaining == 0) {
			$('stw-estimated-time').show();
			$('stw-estimated').update(_stwSecondsToHMS(data.timeRemaining));
		}
	}
}

function _stwSecondsToHMS(d)
{
	d = Number(d);
	var h = Math.floor(d / 3600);
	var m = Math.floor(d % 3600 / 60);
	var s = Math.floor(d % 3600 % 60);
	return ((h > 0 ? h + ':' : '')
			+ (m > 0 ? (h > 0 && m < 10 ? '0' : '') + m + ':' : '0:')
			+ (s < 10 ? '0' : '') + s);
}

function stwHideLevels(target)
{
	var discount_mode = $F('conf[rules][' + target + '][discount_type]');
	if(discount_mode !== 'dynamic_levels') $('conf[rules][' + target  + '][dynamic_levels_discount]').hide();
}

function stwHandleDiscounts(recipient, is_targeting, id) 
{
	var amount_type = (is_targeting) ? $F(recipient + '_' + id + '_amount_type') : $F('conf[rules][' + recipient + '][amount_type]');
	var regexp = /\[.+\%\]/i;
	
	if(!is_targeting) {
		
		$$('#discount_to_' + recipient + ' #tiers_table_' + recipient + ' select').each(function(s) {
			s.childElements().each(function(o) {
				if(o.value !=='' && o.value !=0){
					var matched = regexp.test(o.innerHTML);
					if(amount_type == 'amount_fixed')
					(matched) ? o.writeAttribute('disabled', 'disabled') : o.writeAttribute('disabled', false);
					
					else if(amount_type == 'amount_percent') 
					(matched) ? o.writeAttribute('disabled', false) : o.writeAttribute('disabled', 'disabled');					
					
				}
			});
		});
	}
	else {
		
		$$('#' + recipient + '_' + id + '_amount_type_dynamic_levels select').each(function(s) {
			s.childElements().each(function(o) {
				if(o.value !=='' && o.value !=0){
					var matched = regexp.test(o.innerHTML);
					if(amount_type == 'amount_fixed')
					(matched) ? o.writeAttribute('disabled', 'disabled') : o.writeAttribute('disabled', false);
					
					else if(amount_type == 'amount_percent') 
					(matched) ? o.writeAttribute('disabled', false) : o.writeAttribute('disabled', 'disabled');					
		
				}
			});
		});
	}	
}
