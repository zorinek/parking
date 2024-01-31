  function reset_my_search()
    {
        window.location.href = window.location.origin + window.location.pathname;
    }
    
    function my_search()
    {
        var acc = document.getElementById("accordion");
//        console.log(acc.offsetParent);
        var num = "";
        var search = "";
        var out = new URLSearchParams();
        if(acc.offsetParent == null) //normal
        {
            num = 0;
            var check = document.getElementsByClassName("check");
            var check_res = [];
            for(var i = 0; i < check.length; i++)
            {
                check_res[check[i].getAttribute("data-id")] = check[i].checked;
            }
            console.log("CHECK_RES");
            console.log(check_res);
            var line = document.getElementsByClassName("line");
//            console.log(line);
            for(var i = 0; i < line.length; i++)
            {
//                console.log(line[i].id);
                
//                console.log("HUUUUU");
               
               if(line[i].id in check_res && check_res[line[i].id])
               {
                   search += "&" + line[i].id + "=null" ;
               }
               else
               {
                
                    search += "&" + line[i].id + "=" + line[i].value;
                    out.append(line[i].id, line[i].value);
               }
            }
            
            var select = document.getElementsByClassName("select");
//            console.log("SELECT");
//            console.log(select);
            for(var i = 0; i < select.length; i++)
            {
//                console.log(select[i].id);
                var value = select[i].options[select[i].selectedIndex].value;
                search += "&" + select[i].id + "=" + value;
                out.append(select[i].id, value);
            }
//            search = "";
//            if(event.type == "click")
//            {
                var url = new URL(window.location.href);
                console.log(url.search);
                var up = url.search.slice(1);
                console.log("UP", up);
                var up1 = up.replace(/%5B/g, "[");
                var up2 = up1.replace(/%5D/g, "]");
                up2 = up2.replace(/\[[0-9]\]/g, "[]");
//                up2 = up2.replace('/%/g', "#");
                console.log("UP2", up2);
                var params = new URLSearchParams(up2); 
                var order = params.getAll("order[]");
                var order_dir = params.getAll("order_dir[]");
                console.log(order);
                console.log(order_dir);
                var or = "";
                var ord = "";
                var pref = "";
                var found = false;
                for(var i = 0; i < order.length; i++)
                {
                    if(i == 0)
                    {
                        pref = "";
                    }
                    else
                    {
                        pref = "&";
                    }
                    console.log(order[i]);
                    if(order[i] == event.target.id)
                    {
                        found = true;
                        if(event.target.classList.contains("asc"))
                        {
                            or += pref + "order[]=" + event.target.id;
                            ord += pref + "order_dir[]=asc";
                        }
                        else
                        {
                            or += pref + "order[]=" + event.target.id;
                            ord += pref + "order_dir[]=desc";
                        }
                    }
                    else
                    {
                        or += pref + "order[]=" + order[i];
                        ord += pref + "order_dir[]=" + order_dir[i];
                    }
                }
                if(!found && event.type == "click")
                {
                    if(i == 0)
                    {
                        pref = "";
                    }
                    else
                    {
                        pref = "&";
                    }
                    if(event.target.classList.contains("asc"))
                    {
                        or += pref + "order[]=" + event.target.id;
                        ord += pref + "order_dir[]=asc";
                    }
                    else
                    {
                        or += pref + "order[]=" + event.target.id;
                        ord += pref + "order_dir[]=desc";
                    }
                }
                console.log("SEARCH", search);
//                search = "";
                if(search == "")
                {
                    search = or + "&" + ord;
                }
                else
                {
                    search = or + "&" + ord + "" + search;
                }
                console.log("SEARCH 2", search);
//         
//            }
//            else
//            {
//                console.log("Přidat do url použité parametry pro order, pokud jsou");
//            }
        }
        else //mobile
        {
            num = 1;
            var line = document.getElementsByClassName("line-mobile");
//            console.log(line);
            for(var i = 0; i < line.length; i++)
            {
//                console.log(line[i].id);
                search += "&" + line[i].id + "=" + line[i].value;
            }
            
            var select = document.getElementsByClassName("select-mobile");
//            console.log("SELECT");
//            console.log(select);
            for(var i = 0; i < select.length; i++)
            {
//                console.log(select[i].id);
                var value = select[i].options[select[i].selectedIndex].value;
                search += "&" + select[i].id + "=" + value;
            }
        }
        console.log("NUM");
        console.log(num);
        
//        console.log(out.toString());
//        search += "&" + out.toString();
//        out.toLocaleString();
        console.log(search);
//        search = "&order[]=123&order[]=12345";
//var str = out.toString();
//console.log(str);
//search = 'order%5B0%5D=inf_battery&order_dir%5B0%5D=asc&order%5B0%5D=uni_id&order_dir%5B0%5D=asc';
        document.location.search = search;

//        var xhttp = new XMLHttpRequest();
//          xhttp.onreadystatechange = function() {
//            if (this.readyState == 4 && this.status == 200) {
//              document.getElementById("demo").innerHTML = this.responseText;
//            }
//          };
//          xhttp.open("GET", {*link :Admin:Informations:ajaxSearch*}, true);
//          xhttp.send();
        

    }
    
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})

function export_file(url)
{
    var out = "";
    if(!url.includes("?"))
    {
        out += "?";
    }
    else
    {
        out += "&";
    }
    var cols = document.querySelectorAll(".sel_cols:checked");
    var arr = "";
    for(var i = 0; i < cols.length; i++)
    {
        console.log(cols[i].name);
//                                arr.push(cols[i].name);
        if(i != 0)
        {
            arr += "&";
        }
        arr += "export_cols[]=" + cols[i].name;
    }
    window.location.href = url + out + arr;
}

var dm = Array.prototype.slice.call(document.getElementsByClassName("dropdown-menu-disable-close"));
dm.forEach((elem) => {
    elem.onclick = function (e) { e.stopPropagation();}
});
var di = Array.prototype.slice.call(document.getElementsByClassName("dropdown-item-check"));
di.forEach((elem) => {
    elem.onclick = function (e)
    {
        var check = e.target.querySelector("input[type=checkbox]");
        if(check != null)
        {
            if(check.checked)
            {
                check.checked = false;
            }
            else
            {
                check.checked = true;
            }
        }
    }
});