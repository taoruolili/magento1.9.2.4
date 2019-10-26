var isEmpty = function(value){
  if (value === null || value === undefined || value === '') {
      return true;
  }

  return false;
};

var getPrevSibling = function(node) {
    var tempFirst = node.parentNode.firstChild;
    if (node == tempFirst) return null;
    var tempObj = node.previousSibling;
    while (tempObj.nodeType != 1 && tempObj.previousSibling != null) {
        tempObj = tempObj.previousSibling;
    }
    return (tempObj.nodeType==1)? tempObj:null;
}    


var changeTitleToImage = function(){	
	var imgCtrl = document.getElementById('CreditOnlinePaymentLogo');
	var previousSibling = getPrevSibling(imgCtrl.parentNode);	
	previousSibling.getElementsByTagName('label')[0].innerHTML='<img src="' + imgCtrl.src + '" alt="Visa" title="Neworder Payment">';
};