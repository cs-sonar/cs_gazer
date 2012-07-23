function openprompt(){
	
	jQuery.fn.checked = function(){
		return jQuery(this).attr('checked');
	}
	
	
	var txt ='<label for="summary">サーバー概要</label><input type="text" id="summary" name="summary" value="本屋サービスDBサーバー" /><br />'+
		'<label for="domain">IP・ドメイン</label><input type="text" id="domain" name="domain" value="sonarsrv.com" /><br />'+
		'<input type="checkbox" name="web_check" id="web_check" value="web_check" /><label for="web_check">WEBチェック</label><ul><li>パス sonarsrv.com</li><li>ポート 80</li></ul><br />'+
		'<input type="checkbox" name="ping_check" id="ping_check" value="ping_check" /><label for="ping_check">PINGチェック</label><br />';

	function submitfunc(v,m,f){
		val_summary = m.children('#summary');
		val_domain = m.children('#domain');

		if((f.summary == "") && (f.domain == "")){
			val_summary.css("border","solid #ff0000 1px");
			val_domain.css("border","solid #ff0000 1px");
			return false;
		} else if(f.summary == ""){
			val_summary.css("border","solid #ff0000 1px");
			return false;
		} else if(f.domain == ""){
			val_domain.css("border","solid #ff0000 1px");
			return false;
		}
	return true;
	}

	$.prompt(txt,{
		submit:submitfunc,
		buttons:{OK:true}
	});
} 