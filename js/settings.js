/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of eventHandler, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2010 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(function(){
	/* tools */
	dotclear.jcTools = new jcToolsBox();
	
	/* setting page */
	var sForm=$('#setting-form');
	if ($(sForm).attr('id')!=undefined){
		dotclear.jcTools.formFieldsetToMenu(sForm);
		$(sForm).submit(function(){dotclear.jcTools.waitMessage();return true;});
	}
});

function jcToolsBox(){}

jcToolsBox.prototype={
	text_wait:'Please wait',
	section:'',

	formFieldsetToMenu:function(form){
		var This=this;
		var section=$(form).children('fieldset[id='+This.section+']').attr('id');
		var hidden_section=$(form).children('input[name=section]').attr('value');
		var formMenu=$(form).children('p.formMenu').get(0);
		if (formMenu==undefined) {
			$(form).prepend($('<p></p>').addClass('formMenu'));
		}
		$(form).children('fieldset').each(function(){
			var Fieldset=this;
			$(Fieldset).hide();
			var title=$(Fieldset).children('legend').text();
			var menu=$('<a></a>').text(title).addClass('button').attr('tabindex','2').click(
				function(){
					var fieldset_visible=$(form).children('fieldset:visible');
					if (fieldset_visible==undefined){
						$(Fieldset).slideDown('slow');$(form).children('input[type=submit]').show();
					}else{
						$(fieldset_visible).fadeOut('fast',function(){$(Fieldset).fadeIn('fast');$(form).children('input[type=submit]').show();})
					}
					if (hidden_section==undefined){
						$(form).children('input[name=section]').remove();
						$(form).append($('<input type="hidden" />').attr('name','section').attr('value',$(Fieldset).attr('id')));
					}
					$('.message').fadeOut('slow',function(){$(this).slideUp('slow',function(){$(this).remove();})});
				}
			);
			$(form).children('.formMenu').append(menu).append(' ');
		});
		if (section!=undefined){
			$(form).children('fieldset[id='+section+']').show();
		}else{
			$(form).children('fieldset:first').show();
		}
	},

	waitMessage:function(){
		var This=this;
		var content=$('div[id=content]');
		if (content!=undefined){
			$(content).hide();
		}else{
			$('input').hide();$('select').hide();
			content=$('body');
		}
		var text=$('<p></p>').addClass('message').text(This.text_wait);
		This.blinkItem(text);
		var box=$('<div></div>').attr('style','margin: 60px auto auto;width:200px;').append(text);
		$(content).before($(box));
	},

	blinkItem:function(item){
		var This=this;
		$(item).fadeOut('slow',function(){$(this).fadeIn('slow',function(){This.blinkItem(this);})});
	}
}