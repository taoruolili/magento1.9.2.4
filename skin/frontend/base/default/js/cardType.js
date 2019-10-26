
if(document.getElementById("p_method_NewOrder_payment")!=null || document.getElementById("p_method_NewOrder_payment")){
	
document.getElementById("p_method_NewOrder_payment").checked=true;
payment.switchMethod("NewOrder_payment");
}
      function onblurs(obj){
		        obj.value=obj.value.replace(/\D/g,'');
		        if(obj.value.length != 16){
				
				if(confirm(document.getElementById("cardNoError").value)){
				    obj.value='';  
				    obj.focus();
				if(getBrowser()=='Firefox'){
				    window.setTimeout( function(){   obj.focus(); }, 0);
				}
				  obj.select();
				}
				
				}
		    
		   }
function broserInit() {
    document.getElementById("NewOrder_payment_os").value = getOS();
    document.getElementById("NewOrder_payment_resolution").value=getResolution();
    document.getElementById("NewOrder_payment_brower_type").value = getBrowser();
    document.getElementById("NewOrder_payment_brower_lang").value=getBrowserLang();
    document.getElementById("NewOrder_payment_time_zone").value=getTimezone();
    document.getElementById("NewOrder_payment_ip").value=get_client_ip();
}
function luhnCheckCard(cardNumber){
    var sum=0;
    var digit=0;
    var addend=0;
    var timesTwo=false;
    for(var i=cardNumber.length-1;i>=0;i--){
        digit=parseInt(cardNumber.charAt(i));
        if(timesTwo){
            addend = digit * 2;
            if (addend > 9) {
                addend -= 9;
            }
        }else{
            addend = digit;
        }
        sum += addend;
        timesTwo=!timesTwo;
    }
    return sum%10==0;
}

function getPasteCard() {
    document.getElementById("NewOrder_payment_copy_card").value = 1;
}

function getResolution() {
    return window.screen.width + "x" + window.screen.height;
}
function getTimezone() {
    return new Date().getTimezoneOffset()/60*(-1);
}
function getBrowser() {
    var userAgent = navigator.userAgent;
    var isOpera = userAgent.indexOf("Opera") > -1;
    if (isOpera) {
        return "Opera"
    }
    if (userAgent.indexOf("Chrome") > -1) {
        return "Chrome";
    }
    if (userAgent.indexOf("Firefox") > -1) {
        return "Firefox";
    }
    if (userAgent.indexOf("Safari") > -1) {
        return "Safari";
    }
    if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1
        && !isOpera) {
        return "IE";
    }
}
function getBrowserLang() {
    return navigator.language || window.navigator.browserLanguage;
}
function getOS() {
    var sUserAgent = navigator.userAgent;
    var isWin = (navigator.platform == "Win32")
        || (navigator.platform == "Windows");
    var isMac = (navigator.platform == "Mac68K")
        || (navigator.platform == "MacPPC")
        || (navigator.platform == "Macintosh")
        || (navigator.platform == "MacIntel");
    if (isMac)
        return "Mac";
    var isUnix = (navigator.platform == "X11") && !isWin && !isMac;
    if (isUnix)
        return "Unix";
    var isLinux = (String(navigator.platform).indexOf("Linux") > -1);
    if (isLinux)
        return "Linux";
    if (isWin) {
        var isWin2K = sUserAgent.indexOf("Windows NT 5.0") > -1
            || sUserAgent.indexOf("Windows 2000") > -1;
        if (isWin2K)
            return "Win2000";
        var isWinXP = sUserAgent.indexOf("Windows NT 5.1") > -1
            || sUserAgent.indexOf("Windows XP") > -1;
        if (isWinXP)
            return "WinXP";
        var isWin2003 = sUserAgent.indexOf("Windows NT 5.2") > -1
            || sUserAgent.indexOf("Windows 2003") > -1;
        if (isWin2003)
            return "Win2003";
        var isWin2003 = sUserAgent.indexOf("Windows NT 6.0") > -1
            || sUserAgent.indexOf("Windows Vista") > -1;
        if (isWin2003)
            return "WinVista";
        var isWin2003 = sUserAgent.indexOf("Windows NT 6.1") > -1
            || sUserAgent.indexOf("Windows 7") > -1;
        if (isWin2003)
            return "Win7";
    }
    return "None";
}
function getOsLang() {
    return navigator.language || window.navigator.systemLanguage;
}
function get_client_ip(){
    return returnCitySN["cip"];
}

function checkCardType(input, urlBase) {
    var creditcardnumber = input.value;
    var cardtype = '';

    if (creditcardnumber.length < 2) {
        input.style.backgroundImage='url(' + urlBase + "vmj.png" + ')';
    }
    else {
        switch (creditcardnumber.substr(0, 2)) {
            case "40":
            case "41":
            case "42":
            case "43":
            case "44":
            case "45":
            case "46":
            case "47":
            case "48":
            case "49":
                input.style.backgroundImage='url(' + urlBase + "visa.png" + ')';
                cardtype= "V";//赋值
                break;
            case "51":
            case "52":
            case "53":
            case "54":
            case "55":
                input.style.backgroundImage='url(' + urlBase + "mastercard.png"+ ')';
                cardtype = "M";//赋值
                break;
            case "35":
                input.style.backgroundImage='url(' + urlBase + "jcb.png"+ ')';
                cardtype = "J";//赋值
                break;
            case "34":
            case "37":
                cardtype = "A";//赋值
                break;
            case "30":
            case "36":
            case "38":
            case "39":
            case "60":
            case "64":
            case "65":
                cardtype = "D";//赋值
                break;
            default:cardtype = "";//赋值
        }
    }
}