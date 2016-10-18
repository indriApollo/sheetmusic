function xhrequest(url,args,callback) {

    var req = "/sheetmusic/"+url+"?"+args;
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4) {
            callback(xhr.status,xhr.responseText);
        }
    };
    xhr.open("GET", req);
    xhr.setRequestHeader("Content-type","application/json");
    xhr.send();
}

function filter(filter,idlist) {
        
    for (var i = 0; i < idlist.length; i++) {
        var elid = idlist[i];
        el = document.getElementById(elid);

        //case insensitive and remove spaces
        if(elid.toLocaleLowerCase().indexOf(filter.trim().toLocaleLowerCase()) !== -1)
            el.style.display = "";
        else
            el.style.display = "none";
    }
}

function loadTitles() {
    var url = "inc/sheets_jx.php";
    var args = "cmd=get_titles";
    xhrequest(url,args,function(status,resp) {
        if(status == 200) {
            var cont = document.getElementById("titles");
            titles = JSON.parse(resp);
            for(var i = 0 ;i < titles.length; i++) {
                var title = titles[i];
                var li = document.createElement("li");
                li.id = title;
                li.onclick = function() {loadSheets(this)};
                li.textContent = title;
                cont.appendChild(li);
            }
        } else {
            alert(resp);
        }
    });
}
