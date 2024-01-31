
    var URL = window.URL || window.webkitURL
//  var displayMessage = function (message, isError) {
//    var element = document.querySelector('#message')
//    element.innerHTML = message
//    element.className = isError ? 'error' : 'info'
//  }

//function start_import()
//{
//    measurement_2();
//    document.getElementById("video_col").classList.remove("d-none");
//    document.getElementById("results_col").classList.remove("d-none");
//
//    console.log("here");
//    var importModal = document.getElementById('importModal');
//    var modal = bootstrap.Modal.getInstance(importModal);  
//    modal.hide();
//}

function import_file()
{
    const reader = new FileReader()
    reader.onload = (e) => {
      //document.getElementById('out').innerHTML = reader.result
      console.log(e.target.result);
      var res = e.target.result;
      
      var lines = res.split("\n");
      console.log(lines);
        var splited = lines.map(function(v){
            return v.split(";");
        });
        console.log(splited);
        var settings = JSON.parse(localStorage.getItem("settings"));
        if(settings == null)
        {
            settings = {};
        }
        settings.import = true;
        if(splited[0][1] == 1)
        {
            settings.measurement = 1;
            measurement_type = 1;
            var cross = JSON.parse(localStorage.getItem("cross"));
            if(cross == null)
            {
                cross = [];
            }
            var cnt = 0;
            var i = 0;
            for(var x in splited)
            {
                if(i > 1)
                {
                    if(splited[x].length > 1)
                    {
                        var nt = {
                            id: "",
                            direction: "",
                            direction_to: "",
                            vehicle: "",
                            time: ""
                        };
                        nt.id = cnt;
                        nt.direction = splited[x][0];
                        nt.direction_to = splited[x][1];
                        nt.vehicle = splited[x][2];
                        nt.time = splited[x][3];

                        cross.push(nt);
                        cnt++;
                    }
                }
                i++;
            }
            localStorage.setItem('cross', JSON.stringify(cross));
        }
        else if(splited[0][1] == 2)
        {
            settings.measurement = 2;
            measurement_type = 2;
            var cross_m2 = JSON.parse(localStorage.getItem("cross_m2"));
            if(cross_m2 == null)
            {
                cross_m2 = [];
            }


            var cnt = 0;
            var i = 0;
            for(var x in splited)
            {
                if(i > 1)
                {
                    if(splited[x].length > 1)
                    {
                        console.log(splited[x]);
                        var nt = {
                            id: "",
                            direction: "",
                            vehicle: "",
                            secondary_road_stop: "",
                            main_road_vehicles: [],
                            secondary_road_leave: ""
                        };
                        nt.id = cnt;
                        nt.direction = splited[x][0];
                        nt.vehicle = splited[x][1];
                        nt.secondary_road_stop = splited[x][2];
                        nt.secondary_road_leave = splited[x][3];

                        for(var i = 4; i < splited[x].length ; i++)
                        {
                            nt.main_road_vehicles.push(splited[x][i]);
                        }


                        cross_m2.push(nt);
                        cnt++;
                    }
                }
                i++;
            }
            localStorage.setItem('cross_m2', JSON.stringify(cross_m2));
        }
        localStorage.setItem('settings', JSON.stringify(settings));
    }
  // start reading the file. When it is done, calls the onload event defined above.
  reader.readAsBinaryString(event.target.files[0]);
}


var measurement_type = 1;
var id = 0;
  var playSelectedFile = function (event) {
    var file = this.files[0];
    var type = file.type;
    var videoNode = document.getElementById('video');
     
    var canPlay = videoNode.canPlayType(type);
    if (canPlay === '') canPlay = 'no';

    var fileURL = URL.createObjectURL(file);
    videoNode.src = fileURL;
//    videoNode.addEventListener("readystatechange", (event) => {
//
//    });
//    console.log("VN", videoNode.videoWidth);
    
    var settings = JSON.parse(localStorage.getItem("settings"));
    if(settings == null)
    {
        settings = {};
    }
    
    settings.videoName = file.name;
    
    settings.import = false;
    

    
    if(settings.measurement === undefined)
    {
        
         settings.measurement = 1;
            measurement_type = 1;
    }
    if(settings.lines === undefined)
    {
        settings.lines = [];
        settings.lines_cnt = 0;
    }
    if(settings.lines_body === undefined)
    {
        settings.lines_body = [];
    }
    
    localStorage.setItem('settings', JSON.stringify(settings));
    
//    document.getElementById("video_row").classList.remove("d-none");
  }
  
  function generateLines()
  {
      var cnt = document.getElementById('number_directions').value;

      var sample = document.getElementById('sample');

      var out = "";
      var name = document.getElementById("name");
      var abbr = document.getElementById("abbr");
      for(var i = 1; i <= cnt; i++)
      {
          name.name = "name-" + i;
          name.setAttribute("value", "Směr " + i);
          abbr.name = "abbr-" + i;
          abbr.setAttribute("value", i);
          sample.id = "";
          out += sample.outerHTML;
          sample.id = "sample";
          name.name = "";
          name.value = "";
          abbr.name = "";
          abbr.value = "";

      }
      document.getElementById("directions_lines").innerHTML = out;
  }
  
  var directions = [];
  var vehicles = [];
  var keys_arrows = [];
  var keys_video_sec = [];
  var keys = [];
  var active_direction = "";
  var active_vehicle = "";
  
  function saveDirections()
  {
      var content = document.getElementById("directions_lines");
//      console.log("CNT", content.childElementCount);
      if(content.childElementCount == 0)
      {
            var alert = document.getElementById("no_named_directions");
            alert.classList.remove("d-none");
            setTimeout(function() { 
                alert.classList.add("d-none");
              }, 5000);
      }
      else
      {
      
        var names = document.getElementsByClassName("name");
        var abbrs = document.getElementsByClassName("abbr");

  //      console.log(names);
  //      console.log(abbrs);

        var out = "";
        var cnt = 0;
        for(var x in names)
        {
            if(names[x].name != "" && names[x].name !== undefined && ((names[x].name).split('-')).length > 1)
            {
  //              console.log("ma jmeno", names[x].name);
                var button = document.getElementById("directions_button");
                var button_orig = button.outerHTML;
                button.innerHTML = names[x].value + " <small>(" + abbrs[x].value + ")</small>";

                var sname = (names[x].name).split('-');

                button.id = "button-" + sname[1];
                button.setAttribute("onclick", 'changeDir("' + "button-" + sname[1] + '")');
                out += button.outerHTML;
                document.getElementById('sample_directions_button').innerHTML = button_orig;

                directions.push(abbrs[x].value);
                cnt++;
            }
        }

  //      console.log(out);
        document.getElementById("directions_buttons").innerHTML = out;

        var settings = JSON.parse(localStorage.getItem("settings"));
        if(settings == null)
        {
            settings = {};
        }

        settings.directions_buttons = out;
        localStorage.setItem('settings', JSON.stringify(settings));

        document.getElementById("results_col").classList.remove("d-none");
        document.getElementById("controls_col").classList.remove("d-none");


          var nameDirectionModal = document.getElementById('nameDirectionModal');
          var modal = bootstrap.Modal.getInstance(nameDirectionModal);  
          modal.hide();


        const vehiclesModal = new bootstrap.Modal('#vehiclesModal');
        vehiclesModal.show();
    }
  }
  
  function changeDir(id)
  {
        const parentElement = document.getElementById("directions_buttons");
        const inputs = parentElement.querySelectorAll(`.directions_b`);

        for(var x in inputs)
        {
            console.log(inputs[x]);
            if(inputs[x].id !== undefined)
            {
                inputs[x].classList.remove("btn-warning");
                inputs[x].classList.add("btn-primary");
            }
        }
        
        var but = document.getElementById(id);
//        console.log(but);
        but.classList.remove("btn-primary");
        but.classList.add("btn-warning");
        active_direction = id.split("-")[1];
  }
  
  function changeVehicle(id)
  {
        const parentElement = document.getElementById("vehicles_buttons");
        const inputs = parentElement.querySelectorAll(`.vehicles_b`);

        for(var x in inputs)
        {
//            console.log(inputs[x]);
            if(inputs[x].id !== undefined)
            {
                inputs[x].classList.remove("btn-warning");
                inputs[x].classList.add("btn-primary");
            }
        }
        
        var but = document.getElementById(id);
//        console.log(but);
        but.classList.remove("btn-primary");
        but.classList.add("btn-warning");
        active_vehicle = but.name;
  }
  
//  function directionsKey(ev, value, key)
//  {
////      console.log("E", ev);
////      console.log("V", value);
////      console.log("K", key);
//      if (ev.key === value) {
////                alert('You pressed the "' + key + '" key!');
//                
//                var all = document.getElementsByClassName("directions_b");
////                console.log(all);
//                for(var x in all)
//                {
////                    console.log(all[x].id)
//                    if(all[x].id !== undefined && ((all[x].id).split("-")).length > 1)
//                    {
//                        if( all[x].classList.contains("btn-warning"))
//                        {
//                            all[x].classList.remove("btn-warning");
//                        }
//                        if( !all[x].classList.contains("btn-primary"))
//                        {
//                            all[x].classList.add("btn-primary");
//                        }
//
//                    }
////                    all[x].classList.add("btn-success");
//                }
//                
//                var btn = document.getElementById("button-" + (key+1));
//                btn.classList.remove("btn-primary");
//                btn.classList.add("btn-warning");
//                active_direction = value;
//                console.log("AD", active_direction);
//                
////                setNewLine();
//              }
//      
//  }
  
  
  
    function handleDirections(event)
    {
        if(directions.includes(event.key))
        {
            var all = document.getElementsByClassName("directions_b");
//                console.log(all);
            for(var x in all)
            {
//                    console.log(all[x].id)
                if(all[x].id !== undefined && ((all[x].id).split("-")).length > 1)
                {
                    if( all[x].classList.contains("btn-warning"))
                    {
                        all[x].classList.remove("btn-warning");
                    }
                    if( !all[x].classList.contains("btn-primary"))
                    {
                        all[x].classList.add("btn-primary");
                    }

                }
//                    all[x].classList.add("btn-success");
            }

            var btn = document.getElementById("button-" + (directions.indexOf(event.key)+1));
            btn.classList.remove("btn-primary");
            btn.classList.add("btn-warning");
            active_direction = event.key;
            console.log("AD", active_direction);
        }
    }
    
    function handleVehicles(event)
    {
        if (vehicles.includes(event.key)) {
//                alert('You pressed the "' + key + '" key!');
                
                var all = document.getElementsByClassName("vehicles_b");
//                console.log(all);
                for(var x in all)
                {
//                    console.log(all[x].id)
                    if(all[x].id !== undefined && ((all[x].id).split("-")).length > 1)
                    {
                        if( all[x].classList.contains("btn-warning"))
                        {
                            all[x].classList.remove("btn-warning");
                        }
                        if( !all[x].classList.contains("btn-primary"))
                        {
                            all[x].classList.add("btn-primary");
                        }

                    }
//                    all[x].classList.add("btn-success");
                }
                
                var btn = document.getElementById("buttonv-" + (vehicles.indexOf(event.key)+1));
//                console.log("buttonv-" + (key++1));
//                console.log(btn);
                btn.classList.remove("btn-primary");
                btn.classList.add("btn-warning");
                active_vehicle = btn.name;
//                active_direction = key;
                console.log("AV", active_vehicle);
//                setNewLine();
                
              }
    }
    
    function getIdByValue(value, cn) {
        var elements = document.getElementsByClassName(cn); // Get all elements on the page
        for (var i = 0; i < elements.length; i++) {
          if (elements[i].value === value) { // Check if the value matches
            return elements[i].id; // Return the ID of the matching element
          }
        }
        return null; // Return null if no element with the matching value was found
      }
    
    function handleArrows(event)
    {
        if(keys_arrows.includes(event.key))
        {
            event.preventDefault();
            setNewLine(getIdByValue(event.key, "keys-arrows"));
        }
    }
  
  function saveVehicles()
  {
      var vehicles_tmp = document.getElementsByClassName("vehicles");

      console.log(vehicles_tmp);
      var out = "";
      var cnt = 1;
      for(var x in vehicles_tmp)
      {
          if(vehicles_tmp[x].id != "" && vehicles_tmp[x].id !== undefined)
          {
//              console.log("ma jmeno", names[x].name);
              var button = document.getElementById("vehicles_button");
              var button_orig = button.outerHTML;
              console.log(vehicles_tmp[x].id);
              button.innerHTML = document.getElementById("label-" + vehicles_tmp[x].id).innerHTML + " <small>(" + vehicles_tmp[x].value + ")</small>";
              
              button.id = "buttonv-" + cnt;
              button.name = vehicles_tmp[x].id;
              button.setAttribute("onclick", "changeVehicle('" + "buttonv-" + cnt + "')");
              out += button.outerHTML;
              document.getElementById('sample_vehicles_button').innerHTML = button_orig;
//              var tmp_v = {};
//              tmp_v[vehicles_tmp[x].value] = vehicles_tmp[x].id;
              vehicles.push(vehicles_tmp[x].value);
              cnt++;
          }
      }
      
        document.getElementById("vehicles_buttons").innerHTML = out;
      
      
        

        var first = true;
        directions.forEach(function(value, key) {
            if(first)
            {
                first = false;
                var btn = document.getElementById("button-" + (key+1));
                btn.classList.remove("btn-primary");
                btn.classList.add("btn-warning");
                active_direction = value;
            }
        });
        
        document.addEventListener("keydown", handleDirections);

        first = true;
        vehicles.forEach(function(value, key) {
            
            if(first)
            {
                first = false;
                var btn = document.getElementById("buttonv-" + (key+1));
                btn.classList.remove("btn-primary");
                btn.classList.add("btn-warning");
                active_vehicle = btn.name;
            }
        });
        
        document.addEventListener("keydown", handleVehicles);
        
               
        document.getElementById("start").classList.add("d-none");
//        document.getElementById("start_button").classList.remove("d-none");
        
        document.getElementById("remove_settings_all").classList.remove("d-none");
        
        var settings = JSON.parse(localStorage.getItem("settings"));
        if(settings == null)
        {
            settings = {};
        }
        
        settings.directions = directions;
        settings.vehicles = vehicles;
        settings.vehicles_buttons = out;
        var vid = document.getElementById('video');
        console.log(vid.files);
//        settings.videoName = 
        
        localStorage.setItem('settings', JSON.stringify(settings));
        if(settings.measurement == 1)
        {
            measurement_1();
        }
        else if(settings.measurement == 2)
        {
            measurement_2();
        }
        
        var vehiclesModal = document.getElementById('vehiclesModal');
        var modal = bootstrap.Modal.getInstance(vehiclesModal);  
        modal.hide();
        
        const keysModal = new bootstrap.Modal('#keysModal');
        keysModal.show();
        
  }
  
    function changeArrow(id)
    {
        setNewLine(document.getElementById(id).name);
    }
    
    function changeKeys(number)
    {
        console.log(id);
        var video = document.getElementById("video");
        if(number == 0)
        {
            mcm2_first(video);
        }
        else if(number == 1)
        {
            mcm2_second(video);
        }
    }
  
    function saveKeys()
    {
        var keysinner = document.getElementsByClassName("keys-arrows");

        var out = "";
        var cnt = 0;
        for(var x in keysinner)
        {
            if(keysinner[x].id != "" && keysinner[x].id !== undefined)
            {
                var button = document.getElementById("vehicles_to_button");
                var button_orig = button.outerHTML;
                button.innerHTML = document.getElementById("label-" + keysinner[x].id).innerHTML + " <small>(" + keysinner[x].value + ")</small>";

                button.id = "buttonvt-" + cnt;
                button.name = keysinner[x].id;
                button.setAttribute("onclick", "changeArrow('" + "buttonvt-" + cnt + "')");
                out += button.outerHTML;
                document.getElementById('sample_vehicles_to_button').innerHTML = button_orig;
                
                keys_arrows.push(keysinner[x].value);
                cnt++;
            }
        }
        
        var keysinner = document.getElementsByClassName("keys");

        var out1 = "";
        var cnt = 0;
        for(var x in keysinner)
        {
            if(keysinner[x].id != "" && keysinner[x].id !== undefined)
            {
                var button = document.getElementById("vehicles_road_button");
                var button_orig = button.outerHTML;
                button.innerHTML = document.getElementById("label-" + keysinner[x].id).innerHTML + " <small>(" + keysinner[x].value + ")</small>";

                button.id = "buttonrt-" + cnt;
                button.name = keysinner[x].id;
                button.setAttribute("onclick", "changeKeys('" + cnt + "')");
                out1 += button.outerHTML;
                document.getElementById('sample_vehicles_road_button').innerHTML = button_orig;
                
                keys.push(keysinner[x].value);
                cnt++;
            }
        }
        
        var settings = JSON.parse(localStorage.getItem("settings"));
        if(settings == null)
        {
            settings = {};
        }
        
        if(settings.measurement == 1)
        {
            document.getElementById("vehicles_to_buttons").innerHTML = out;
        }
        else if(settings.measurement == 2)
        {
            document.getElementById("vehicles_road_buttons").innerHTML = out1;
        }
        document.addEventListener("keydown", handleArrows);
        
        var keysinner = document.getElementsByClassName("keys-video-sec");

        for(var x in keysinner)
        {
            if(keysinner[x].id != "" && keysinner[x].id !== undefined)
            {
                keys_video_sec.push(keysinner[x].value);
            }
        }
        document.addEventListener("keydown", handleVideoKeysSeconds);
      
        
        settings.keys_arrows = keys_arrows;
        settings.keys_video_sec = keys_video_sec;
        settings.keys = keys;
        settings.vehicles_to_buttons = out;
        settings.vehicles_road_buttons = out1;
        
        localStorage.setItem('settings', JSON.stringify(settings));
        
        var pm = document.getElementsByClassName("plusminus");
        console.log(pm);
        for (var i = 0; i < pm.length; i++)
        {
            console.log(pm[i]);
            pm[i].addEventListener("click", videoSeconds, false);
        }
       
        
        var keysModal = document.getElementById('keysModal');
        var modal = bootstrap.Modal.getInstance(keysModal);  
        modal.hide();
    }
  
    function changeDirection()
    {
        const parentElement = document.getElementById("directions_buttons");
        const inputs = parentElement.querySelectorAll(`.directions_b`);

        var next = false;
        for(var x in inputs)
        {
            if(inputs[x].classList !== undefined && next == false && inputs[x].classList.contains("btn-warning"))
            {
                inputs[x].classList.remove("btn-warning");
                inputs[x].classList.add("btn-primary");
                next = true;
                continue;
            }
            
            if(inputs[x].classList !== undefined && next)
            {
                next = false;
                inputs[x].classList.remove("btn-primary");
                inputs[x].classList.add("btn-warning");
                active_direction = ((inputs[x].id).split('-'))[1];
            }  
        }
        
        if(next)
        {
            next = false;
            inputs[0].classList.remove("btn-primary");
            inputs[0].classList.add("btn-warning");
            active_direction = ((inputs[0].id).split('-'))[1];
        }

    }
  
 
    function setNewLine(direction_to)
    {
        var time = document.getElementById("video").currentTime;
        var data = {
            id: id,
            direction: active_direction,
            direction_to: direction_to,
            vehicle: active_vehicle,
            time: time
        };
        console.log(data);
        var stor = JSON.parse(localStorage.getItem("cross"));
        if(stor == null || stor.length == 0)
        {
            stor = [];
        }
        
        stor.push(data);
        
        localStorage.setItem('cross', JSON.stringify(stor));
        
        var html = "<tr id='" + id + "'><td type='direction' ondblclick='edit_cell()'>" + active_direction + "</td><td type='direction_to' ondblclick='edit_cell()'>" + direction_to + "</td><td type='vehicle' ondblclick='edit_cell()'>" + active_vehicle + "</td><td><span class='btn btn-link p-0' onclick='to_video_time();'>" + time + "</span></td></tr>";
        document.getElementById("measurement_body").insertAdjacentHTML("afterbegin", html);
        changeVehicle("buttonv-1");
        id++;
    }
    
    function backVideo()
    {
        var directionsModal = document.getElementById('nameDirectionModal');
        var modal = bootstrap.Modal.getInstance(directionsModal);  
        modal.hide();
        
        const loadVideoModal = new bootstrap.Modal('#loadVideoModal');
        loadVideoModal.show();
    }
    
    function backDirections()
    {
        var vehiclesModal = document.getElementById('vehiclesModal');
        var modal = bootstrap.Modal.getInstance(vehiclesModal);  
        modal.hide();

        const directionsModal = new bootstrap.Modal('#nameDirectionModal');
        directionsModal.show();
    }
    
    function backVehicles()
    {
        var keysModal = document.getElementById('keysModal');
        var modal = bootstrap.Modal.getInstance(keysModal);  
        modal.hide();

        const vehiclesModal = new bootstrap.Modal('#vehiclesModal');
        vehiclesModal.show();
    }
    
    function toDirections()
    {
        var video = document.getElementById("video");

        console.log("VIDEO_SRC", video.src);
        if(video.src == "")
        {
            var alert = document.getElementById("no_video_selected_alert");
            alert.classList.remove("d-none");
            setTimeout(function() {
                alert.classList.add("d-none");
              }, 5000);
        }
        else
        {
             var settings = JSON.parse(localStorage.getItem("settings"));
            if(settings == null)
            {
                settings = {};
            }
            if(settings.import == true)
            {
                if(settings.measurement == 1)
                {
                    document.getElementById("measurement_1").classList.remove("btn-primary");
                    document.getElementById("measurement_1").classList.add("btn-warning");
                }
                else if(settings.measurement == 2)
                {
                    document.getElementById("measurement_2").classList.remove("btn-primary");
                    document.getElementById("measurement_2").classList.add("btn-warning");
                }
            }
            else
            {
                if(document.getElementById("opt_m_1").checked)
                {
                    settings.measurement = 1;
                    measurement_type = 1;
                    document.getElementById("measurement_1").classList.remove("btn-primary");
                    document.getElementById("measurement_1").classList.add("btn-warning");
                }
                else
                {
                    settings.measurement = 2;
                    measurement_type = 2;
                    document.getElementById("measurement_2").classList.remove("btn-primary");
                    document.getElementById("measurement_2").classList.add("btn-warning");
                }
            }
            localStorage.setItem('settings', JSON.stringify(settings));
            
            document.getElementById("video_col").classList.remove("d-none");
            
            var loadVideoModal = document.getElementById('loadVideoModal');
            var modal = bootstrap.Modal.getInstance(loadVideoModal);  
            modal.hide();

            const directionsModal = new bootstrap.Modal('#nameDirectionModal');
            directionsModal.show();
        }
    }
    
    function csvResults()
    {
        var settings = JSON.parse(localStorage.getItem("settings"));
        if(settings == null)
        {
            settings = {};
        }
        const currentDate = new Date();
        const dateStr = currentDate.toISOString().slice(0, 19).replace(/[-T]/g, '').replace(/:/g, '');
        let csvString = '';
        csvString += "typ_mereni;" + measurement_type + ";nazev_videa;" + settings.videoName + "\n";
        let fileName = "";
        if(measurement_type === 1)
        {
            const data = JSON.parse(localStorage.getItem('cross'));
            // Create the file name
            fileName = `${dateStr}-crossroads.csv`;
            // Create a CSV string from the data
            
            csvString += "reseny_smer;smer_kam_vozidlo_jede;vozidlo;cas_videa\n";
            data.forEach((item) => {
              csvString += `${item.direction};${item.direction_to};${item.vehicle};${item.time}\n`;
            });
        }
        else if(measurement_type === 2)
        {
            const data = JSON.parse(localStorage.getItem('cross_m2'));
            // Create the file name
            fileName = `${dateStr}-crossroads-distances.csv`;
            // Create a CSV string from the data
            
            csvString += "reseny_smer;vozidlo;cas_prijezdu_na_stop_caru;cas_opusteni_stop_cary;prujezdy_nadrazenych_vozidel\n";
            data.forEach((item) => {
                csvString += `${item.direction};${item.vehicle};${item.secondary_road_stop};${item.secondary_road_leave}`;
                item.main_road_vehicles.forEach((it) => {
                    csvString += `;${it}`;
                });
                csvString += "\n";
            });
        }

        // Create a blob object from the CSV string
        const blob = new Blob([csvString], { type: 'text/csv;charset=windows-1250;' });

        // Create a download link element
        const downloadLink = document.createElement('a');
        downloadLink.setAttribute('href', URL.createObjectURL(blob));
        downloadLink.setAttribute('download', fileName);

        // Append the link to the document body
        document.body.appendChild(downloadLink);

        // Click the download link to initiate download
        downloadLink.click();
    }
    
    function beginCrossroadsAuto()
    {
        var settings = JSON.parse(localStorage.getItem("settings"));
        if(settings !== null)
        {
//            alert("Uložená data");
            var vid = document.getElementById("saved_video");
            vid.innerHTML = "Poslední použité video bylo: " + settings.videoName;
            vid.classList.remove("d-none");
            document.getElementById("to_directions").classList.add("d-none");
            document.getElementById("start").classList.add("d-none");
            document.getElementById("to_load_settings").classList.remove("d-none");
            document.getElementById("remove_settings").classList.remove("d-none");
            document.getElementById("measurement_select").classList.add("d-none");
            const loadVideoModal = new bootstrap.Modal('#loadVideoModal');
            loadVideoModal.show();
        }
        else
        {
//            
        }
    }
    function beginCrossroads()
    {
//        localStorage.removeItem("cross");
            const loadVideoModal = new bootstrap.Modal('#loadVideoModal');
            loadVideoModal.show();
    }
    
    function toLoadSettings()
    {
        if(video.src == "")
        {
            var alert = document.getElementById("no_video_selected_alert");
            alert.classList.remove("d-none");
            setTimeout(function() {
                alert.classList.add("d-none");
              }, 5000);
        }
        else
        {
            
           
            var settings = JSON.parse(localStorage.getItem("settings"));
            document.getElementById("directions_buttons").innerHTML = settings.directions_buttons;
            document.getElementById("vehicles_buttons").innerHTML = settings.vehicles_buttons;
            document.getElementById("vehicles_to_buttons").innerHTML = settings.vehicles_to_buttons;
            document.getElementById("vehicles_road_buttons").innerHTML = settings.vehicles_road_buttons;
            directions = settings.directions;
            vehicles = settings.vehicles;
            keys_arrows = settings.keys_arrows;
            keys_video_sec = settings.keys_video_sec;
            keys = settings.keys;
            var first = true;
            directions.forEach(function(value, key) {
                if(first)
                {
                    first = false;
                    var btn = document.getElementById("button-" + (key+1));
                    btn.classList.remove("btn-primary");
                    btn.classList.add("btn-warning");
                    active_direction = value;
                }
            });

            document.addEventListener("keydown", handleDirections);

            first = true;
            vehicles.forEach(function(value, key) {

                if(first)
                {
                    first = false;
                    var btn = document.getElementById("buttonv-" + (key+1));
                    btn.classList.remove("btn-primary");
                    btn.classList.add("btn-warning");
                    active_vehicle = btn.name;
                }
            });

            document.addEventListener("keydown", handleVehicles);
            
             var keysinner = document.getElementsByClassName("keys-video_sec");
                var cnt = 0;
                for (var x in keysinner)
                {
                    keysinner[x].value = keys_video_sec[cnt];
                    cnt++;
                }
                
                document.addEventListener("keydown", handleVideoKeysSeconds);
                
                var pm = document.getElementsByClassName("plusminus");
//        console.log(pm);
                for (var i = 0; i < pm.length; i++)
                {
        //            console.log(pm[i]);
                    pm[i].addEventListener("click", videoSeconds, false);
                }
            
            if(settings.measurement == 1)
            {
                var keysinner = document.getElementsByClassName("keys-arrows");
                var cnt = 0;
                for (var x in keysinner)
                {
                    keysinner[x].value = keys_arrows[cnt];
                    cnt++;
                }
                
                document.addEventListener("keydown", handleArrows);
                measurement_1();
            }
            else if(settings.measurement == 2)
            {
//                var keysinner = document.getElementsByClassName("keys");
//                var cnt = 0;
//                for (var x in keysinner)
//                {
//                    keysinner[x].value = keys[cnt];
//                    cnt++;
//                }
                
//                document.addEventListener("keydown", handleArrows);

                measurement_2();
            }

            document.getElementById("video_col").classList.remove("d-none");
            document.getElementById("results_col").classList.remove("d-none");
            document.getElementById("controls_col").classList.remove("d-none");
//            document.getElementById("start_button").classList.remove("d-none");
            document.getElementById("remove_settings_all").classList.remove("d-none");

            var bb = video.parentElement.getBoundingClientRect();
            var canvas = document.getElementById("canvas");
            canvas.width = bb.width;
            canvas.height = bb.height;
//            console.log("VN", video.videoWidth);
            
            if(settings.lines.length > 0)
            {
//                const canvas = document.querySelector('#canvas');

                if (!canvas.getContext) {
                    return;
                }
                const ctx = canvas.getContext('2d');
                settings.lines.forEach(function(value, key) {
                   
                    console.log(key);
                    console.log(value);
                    
                    

                    // set line stroke and line width
                    if(value.type == "primary")
                    {
                        ctx.strokeStyle = 'red';
                    }
                    else if (value.type == "secondary")
                    {
                        ctx.strokeStyle = 'blue';
                    }
                    ctx.lineWidth = 5;

                    // draw a red line
                    ctx.beginPath();
                    ctx.moveTo(value.coords[0].x, value.coords[0].y);
                    ctx.lineTo(value.coords[1].x, value.coords[1].y);
                    ctx.stroke();
                    
                });
                canvas.style.zIndex = 10;
                
                
                    var out = "";
                    settings.lines_body.forEach(function(value, key) {
                 
                        out += value;
                    });
                document.getElementById("lines_body").innerHTML = out;
                lines_cnt = settings.lines_cnt;
                
            }


            var loadVideoModal = document.getElementById('loadVideoModal');
            var modal = bootstrap.Modal.getInstance(loadVideoModal);  
            modal.hide();
        }

        
    }
    
  function closePopover() 
  {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return bootstrap.Popover.getInstance(popoverTriggerEl)
    })
    popoverList.forEach(function (popover) {
      popover.hide()
    })
  }
  
    function removeData()
    {
      localStorage.removeItem("cross");
      id = 0;
      document.getElementById("measurement_body").innerHTML = "";
      closePopover();
    }
  
    function removeConfigurationData()
    {
        closePopover();
        localStorage.removeItem("cross");
        localStorage.removeItem("cross_m2");
        localStorage.removeItem("settings");
        document.getElementById("measurement_body").innerHTML = "";
        document.getElementById("directions_buttons").innerHTML = "";
        document.getElementById("vehicles_buttons").innerHTML = "";
        document.getElementById("saved_video").innerHTML = "";
        document.getElementById("saved_video").classList.add("d-none");
        document.getElementById("remove_settings").classList.add("d-none");
        document.getElementById("to_load_settings").classList.add("d-none");
        document.getElementById("to_directions").classList.remove("d-none");
        document.getElementById("measurement_1").classList.remove("btn-warning");
        document.getElementById("measurement_2").classList.remove("btn-warning");
        document.getElementById("measurement_1").classList.add("btn-primary");
        document.getElementById("measurement_2").classList.add("btn-primary");
        document.getElementById("measurement_select").classList.remove("d-none");
//        document.getElementById("opt_m_1").setAttribute("checked", "");
//        document.getElementById("opt_m_2").removeAttribute("checked");
        document.getElementById("video_form").reset();
        
        
        
        var bb = video.parentElement.getBoundingClientRect();
        var canvas = document.getElementById("canvas");
        canvas.width = bb.width;
        canvas.height = bb.height;
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.restore();
        
        document.getElementById("lines_body").innerHTML = "";
        
        directions = [];
        vehicles = [];
        keys_arrows = [];
        keys_video_sec = [];
        keys = [];
        active_direction = "";
        active_vehicle = "";
        measurement_type = 1;
        lines_cnt = 1;
        
        var loadVideoModal = document.getElementById('loadVideoModal');
        var modal = bootstrap.Modal.getInstance(loadVideoModal);  
        modal.hide();
        
        document.getElementById("start").classList.remove("d-none");
        document.getElementById("video_col").classList.add("d-none");
        document.getElementById("results_col").classList.add("d-none");
        document.getElementById("controls_col").classList.add("d-none");
        document.getElementById("inputVideo").value = "";
        document.getElementById("directions_lines").innerHTML = "";
        document.getElementById("vehicles_form").reset();
        document.getElementById("keys_form").reset();
        document.getElementById("video").removeAttribute('src');
        document.getElementById("video").load();
    }
    
    
    function videoControl()
    {
        var video = document.getElementById("video");
        
        if (event.key === " ") 
        {
            video.blur();
            event.preventDefault();
            
            if(video.paused)
            {
                video.play();
            }
            else
            {
                video.pause();
            }
        }
//        else if(event.shiftKey && event.key === '+')
//        {
//            event.preventDefault();
//            if(video.currentTime <= video.duration - 10)
//            {
//                video.currentTime = video.currentTime + 10;
//            }
//            else
//            {
//                video.currentTime = video.duration;
//            }
//        }
//        else if(event.shiftKey && event.key === '-')
//        {
//            event.preventDefault();
//            if(video.currentTime > 0 + 10)
//            {
//                video.currentTime = video.currentTime - 10;
//            }
//            else
//            {
//                video.currentTime = 0;
//            }
//        }
        else if(event.key === "+")
        {
            var speed = document.getElementById("speed");
            if (video.playbackRate < 16) {
                video.playbackRate += 0.25;
                speed.innerHTML = video.playbackRate;
            }
        }
        else if(event.key === "-")
        {
            var speed = document.getElementById("speed");
            if (video.playbackRate > 0.5) {
                video.playbackRate -= 0.25;
                speed.innerHTML = video.playbackRate;
            }
        }
        else if(event.key === "Escape")
        {
            var speed = document.getElementById("speed");
            video.playbackRate = 1.0;
            speed.innerHTML = video.playbackRate;
        }
        
        
 
    }
    
    function playPause()
    {
        var video = document.getElementById("video");
        video.blur();
        event.preventDefault();

        if(video.paused)
        {
            video.play();
        }
        else
        {
            video.pause();
        }
    }
    
    function handleVideoKeysSeconds()
    {
        if(keys_video_sec.includes(event.key))
        {
            event.preventDefault();
            var id = getIdByValue(event.key, "keys-video-sec");
            var video = document.getElementById("video");
            console.log(id);
            var val = document.getElementById(id).getAttribute("val");
            video.currentTime = video.currentTime + parseFloat(val);
        }
    }
    
    
    function videoSeconds()
    {
        var val = event.target.getAttribute("val");
        var video = document.getElementById("video");
        video.currentTime = video.currentTime + parseFloat(val);
    }
    
    function displayStart()
    {
        document.getElementById("start").classList.remove("d-none");
        document.getElementById("start_button").classList.add("d-none");
    }
    
    function hideStart()
    {
        document.getElementById("start").classList.add("d-none");
        document.getElementById("start_button").classList.remove("d-none");
    }
    
    function edit_cell()
    {
        console.log(event.target.parentElement.id);
        console.log(event.target.getAttribute("contenteditable"));
        console.log(event.target);
        if(measurement_type === 1)
        {
            if(event.target.getAttribute("contenteditable"))
            {
                event.target.removeAttribute("contenteditable");
                event.target.classList.remove("bg-warning");
                const data = JSON.parse(localStorage.getItem('cross'));
    //            console.log(data);
                let item = data.find((o, i) => {
                    if(o.id == event.target.parentElement.id)
                    {
                        data[i][event.target.getAttribute('type')] = event.target.innerHTML;
                        console.log(data[i]);
                    }
                });
    //            item[event.target.getAttribute('type')] = event.target.innerHTML;
                localStorage.setItem('cross', JSON.stringify(data));
                document.addEventListener("keydown", handleVehicles);
                document.addEventListener("keydown", handleDirections);
                document.addEventListener("keydown", handleArrows);
            }
            else if(event.target.getAttribute("contenteditable") === false || event.target.getAttribute("contenteditable") === null)
            {
    //            console.log("here");
                event.target.setAttribute("contenteditable", true);
                event.target.classList.add("bg-warning");
                document.removeEventListener("keydown", handleVehicles);
                document.removeEventListener("keydown", handleDirections);
                document.removeEventListener("keydown", handleArrows);
            }   
        }
        else if(measurement_type === 2)
        {
            if(event.target.getAttribute("contenteditable"))
            {
                event.target.removeAttribute("contenteditable");
                event.target.classList.remove("bg-warning");
                const data = JSON.parse(localStorage.getItem('cross_m2'));
    //            console.log(data);
                let item = data.find((o, i) => {
                    if(o.id == event.target.parentElement.id)
                    {
                        data[i][event.target.getAttribute('type')] = event.target.innerHTML;
                        console.log("D1",data[i]);
                    }
                });
    //            item[event.target.getAttribute('type')] = event.target.innerHTML;
                localStorage.setItem('cross_m2', JSON.stringify(data));
                document.addEventListener("keydown", handleVehicles);
                document.addEventListener("keydown", handleDirections);
                document.addEventListener("keydown", handleArrows);
            }
            else if(event.target.getAttribute("contenteditable") === false || event.target.getAttribute("contenteditable") === null)
            {
    //            console.log("here");
                event.target.setAttribute("contenteditable", true);
                event.target.classList.add("bg-warning");
                document.removeEventListener("keydown", handleVehicles);
                document.removeEventListener("keydown", handleDirections);
                document.removeEventListener("keydown", handleArrows);
            } 
        }
    }
    
    
    var m2_pos = 0;   
    var m2_cnt = 1;
    var m2_id = 0;
    var m2_data = {
        id : "",
        direction : "",
        vehicle : "",
        secondary_road_stop : "",
        main_road_vehicles : [],
        secondary_road_leave : ""
    };
    
    function measurement_1()
    {
        measurement_type = 1;
        document.addEventListener("keydown", handleArrows);

        var cross = JSON.parse(localStorage.getItem("cross"));
        var out = "";
        for(var x in cross)
        {
            out = "<tr id='" + cross[x].id + "'><td type='direction' ondblclick='edit_cell()'>" + cross[x].direction + "</td><td type='direction_to' ondblclick='edit_cell()'>" + cross[x].direction_to + "</td><td type='vehicle' ondblclick='edit_cell()'>" + cross[x].vehicle + "</td><td><span class='btn btn-link p-0' onclick='to_video_time();'>" + cross[x].time + "</span></td></tr>" + out;
        }
        document.getElementById("measurement_body").innerHTML = out;
        if(cross !== null)
        {
            id = cross.length;
        }
        else 
        {
            id = 0;
        }
        document.getElementById("measurement_1").classList.remove("btn-primary");
        document.getElementById("measurement_1").classList.add("btn-warning");
        
        document.getElementById("tr_m_2").classList.add("d-none");
        document.getElementById("tr_m_1").classList.remove("d-none");
        
        var m1 = document.getElementById("measurement_1");
        m1.classList.add("btn-warning");
        m1.classList.remove("btn-primary");
        var m2 = document.getElementById("measurement_2");
        m2.classList.remove("btn-warning");
        m2.classList.add("btn-primary");
        
        document.getElementById("lines_footer").classList.add("d-none");
//        document.getElementById("measurement_body").innerHTML = "";
        
        document.addEventListener("keydown", handleArrows);
        
//        document.removeEventListener('keydown', videoControl);
        document.removeEventListener('keydown', measurementControlM2);
        
        document.getElementById("m2_buttons").classList.add("d-none");
        document.getElementById("m1_buttons").classList.remove("d-none");
        
        var settings = JSON.parse(localStorage.getItem("settings"));
        if(settings == null)
        {
            settings = {};
        }

        settings.measurement = 1;
        localStorage.setItem('settings', JSON.stringify(settings));
        
        var bb = video.parentElement.getBoundingClientRect();
        var canvas = document.getElementById("canvas");
        canvas.width = bb.width;
        canvas.height = bb.height;
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.restore();
        
    }
    
    function measurement_2()
    {  
        measurement_type = 2;
        document.getElementById("measurement_2").classList.remove("btn-primary");
        document.getElementById("measurement_2").classList.add("btn-warning");
        
        document.getElementById("tr_m_1").classList.add("d-none");
        document.getElementById("tr_m_2").classList.remove("d-none");
        
        var m1 = document.getElementById("measurement_1");
        m1.classList.remove("btn-warning");
        m1.classList.add("btn-primary");
        var m2 = document.getElementById("measurement_2");
        m2.classList.add("btn-warning");
        m2.classList.remove("btn-primary");
        
        document.getElementById("measurement_body").innerHTML = "";
        
        document.removeEventListener("keydown", handleArrows);
        
//        document.removeEventListener('keydown', videoControl);
        document.addEventListener('keydown', measurementControlM2);
        
        
        var stor = JSON.parse(localStorage.getItem("cross_m2"));
        
        if(stor !== null)
        {
            m2_id = stor.length;
        }
        else 
        {
            m2_id = 0;
        }
        
        var out = "";
        for(var y in stor)
        {
            var tmp = "<tr id='" + stor[y].id + "'>";
            tmp += "<td type='direction' ondblclick='edit_cell()'>" + stor[y].direction + "</td><td type='vehicle' ondblclick='edit_cell()'>" + stor[y].vehicle + "</td><td><span class='btn btn-link p-0' onclick='to_video_time();'>" + stor[y].secondary_road_stop + "</span></td><td><span class='btn btn-link p-0' onclick='to_video_time();'>" + stor[y].secondary_road_leave + "</span></td>";
            tmp += "<td>";
            var c = true;
            for(var x in stor[y].main_road_vehicles)
            {
                if(c)
                {
                    c = false;
                }
                else
                {
                  tmp += ", ";   
                }
                tmp += "<span class='btn btn-link p-0' onclick='to_video_time();' id='" + m2_cnt + "-" + x + "'>" + stor[y].main_road_vehicles[x] + "</span>";
            }
            tmp += "</td>";
            tmp += "</tr>";
            out = tmp + out;
        }
        
        document.getElementById("measurement_body").innerHTML = out;
            
        document.getElementById("lines_footer").classList.remove("d-none");
        
        var settings = JSON.parse(localStorage.getItem("settings"));
        
        var bb = video.parentElement.getBoundingClientRect();
            var canvas = document.getElementById("canvas");
            canvas.width = bb.width;
            canvas.height = bb.height;

            if(settings !== null && settings.lines.length > 0)
            {
//                const canvas = document.querySelector('#canvas');

                if (!canvas.getContext) {
                    return;
                }
                const ctx = canvas.getContext('2d');
                settings.lines.forEach(function(value, key) {
                   
                    console.log(key);
                    console.log(value);
                    
                    

                    // set line stroke and line width
                    if(value.type == "primary")
                    {
                        ctx.strokeStyle = 'red';
                    }
                    else if (value.type == "secondary")
                    {
                        ctx.strokeStyle = 'blue';
                    }
                    ctx.lineWidth = 5;

                    // draw a red line
                    ctx.beginPath();
                    ctx.moveTo(value.coords[0].x, value.coords[0].y);
                    ctx.lineTo(value.coords[1].x, value.coords[1].y);
                    ctx.stroke();
                    
                });
                canvas.style.zIndex = 10;
            }
        document.getElementById("m1_buttons").classList.add("d-none");
        document.getElementById("m2_buttons").classList.remove("d-none");
        
        if(settings == null)
        {
            settings = {};
        }

        settings.measurement = 2;
        localStorage.setItem('settings', JSON.stringify(settings));
    }
    
    function mcm2_first(video)
    {
        event.preventDefault();
        if(m2_pos == 0)
        {
            m2_data.id = m2_id;
            m2_data.direction = active_direction;
            m2_data.vehicle = active_vehicle;
            m2_data.secondary_road_stop = video.currentTime;
            m2_pos = 1;
        }
        else if(m2_pos == 1)
        {
            m2_data.main_road_vehicles.push(video.currentTime);
        }
    }
    
    function mcm2_second(video)
    {
        event.preventDefault();
        if(m2_pos == 1)
        {
            m2_data.secondary_road_leave = video.currentTime;
            m2_pos = 2;
        }
        else if(m2_pos == 2)
        {
            m2_data.main_road_vehicles.push(video.currentTime);


            var out = "<tr id='" + m2_id + "'>";
            out += "<td type='direction' ondblclick='edit_cell()'>" + m2_data.direction + "</td><td type='vehicle' ondblclick='edit_cell()'>" + m2_data.vehicle + "</td><td><span class='btn btn-link p-0' onclick='to_video_time();'>" + m2_data.secondary_road_stop + "</span></td><td><span class='btn btn-link p-0' onclick='to_video_time();'>" + m2_data.secondary_road_leave + "</span></td>";
            out += "<td>";
            var c = true;
            for(var x in m2_data.main_road_vehicles)
            {
                if(c)
                {
                    c = false;
                }
                else
                {
                  out += ", ";   
                }
                out += "<span class='btn btn-link p-0' onclick='to_video_time();' id='" + m2_cnt + "-" + x + "'>" + m2_data.main_road_vehicles[x] + "</span>";
            }
            out += "</td>";
            out += "</tr>";
//                console.log(out);

            var mb = document.getElementById("measurement_body");
            mb.insertAdjacentHTML("afterbegin", out);

            var stor = JSON.parse(localStorage.getItem("cross_m2"));
            if(stor == null || stor.length == 0)
            {
                stor = [];
            }

            stor.push(m2_data);

            localStorage.setItem('cross_m2', JSON.stringify(stor));
            m2_id++;

            m2_pos = 0;
            m2_data = {
                direction : "",
                vehicle : "",
                secondary_road_stop : "",
                main_road_vehicles : [],
                secondary_road_leave : ""
            };
            changeVehicle("buttonv-1");
        }
    }
    
    function measurementControlM2()
    {
        var video = document.getElementById("video");
//        console.log(event.key);
        if (event.key === keys[0]) 
        {
            mcm2_first(video);
        }
        else if(event.key === keys[1])
        {
            mcm2_second(video);
        }
        else if(event.key === keys[2])
        {
            event.preventDefault();

        }
    }
    
//    var lines = [];
    var lineStart = false;
    var lineEnd = false;
    var lineType = "";
    var tempLine = {"id": 0, "coords" : [{"x" : 0, "y" : 0}, {"x" : 0, "y" : 0}], "type" : ""};
    var lines_cnt = 1;
    
    function setLine(type)
    {
        lineStart = true;
        lineType = type;
        document.getElementById("canvas").style.zIndex = -10;
    }
    
    function setCoords(event)
    {
        event.preventDefault();
        console.log(event);
        if(lineStart)
        {
            lineStart = false;
            lineEnd = true;
            tempLine.coords[0].x = event.offsetX;
            tempLine.coords[0].y = event.offsetY;
            tempLine.type = lineType;
        }
        else if (lineEnd)
        {
            lineEnd = false;
            tempLine.coords[1].x = event.offsetX;
            tempLine.coords[1].y = event.offsetY;
            tempLine.id = lines_cnt;
//            lines.push(tempLine);
            
            document.getElementById("canvas").style.zIndex = 10;
//            console.log(lines);
            
            const canvas = document.querySelector('#canvas');

            if (!canvas.getContext) {
                return;
            }
            const ctx = canvas.getContext('2d');

            // set line stroke and line width
            if(lineType == "primary")
            {
                ctx.strokeStyle = 'red';
            }
            else if (lineType == "secondary")
            {
                ctx.strokeStyle = 'blue';
            }
            ctx.lineWidth = 5;

            // draw a red line
            ctx.beginPath();
            ctx.moveTo(tempLine.coords[0].x, tempLine.coords[0].y);
            ctx.lineTo(tempLine.coords[1].x, tempLine.coords[1].y);
            ctx.stroke();
            
            var out = "";
            out += "<tr><td>" + lines_cnt + "</td><td>" + tempLine.coords[0].x + ", " + tempLine.coords[0].y + "</td><td>" + tempLine.coords[1].x + ", " + tempLine.coords[1].y + "</td><td>" + lineType + "</td><td><button class='btn btn-danger' val='" + lines_cnt + "' onclick='remove_line();'>X</button></td></tr>";
            
            document.getElementById("lines_body").insertAdjacentHTML("beforeend", out);
            
          
            var settings = JSON.parse(localStorage.getItem("settings"));
            if(settings == null)
            {
                settings = {};
            }

            settings.lines_body.push(out);
            settings.lines.push(tempLine);
            settings.lines_cnt = lines_cnt;
            localStorage.setItem('settings', JSON.stringify(settings));
            lines_cnt++;
            
            
            tempLine = { "id": 0, "coords" : [{"x" : 0, "y" : 0}, {"x" : 0, "y" : 0}], "type" : ""};
            
            
            
//            var xhttp = new XMLHttpRequest();
//            xhttp.onreadystatechange = function() {
//                if (this.readyState == 4 && this.status == 200) 
//                {
//                   // Typical action to be performed when the document is ready:
//                   var result = JSON.parse(xhttp.responseText)
//                   document.getElementById("lines").innerHTML = result.lines;
//                }
//            };
//            xhttp.open("POST", {link :Crossroads:Homepage:lines}, true);
//            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//            xhttp.send("json=" + JSON.stringify(lines));
        }
    }
    
    function remove_line()
    {
        console.log(event);
        var id = event.target.getAttribute("val");
        
        var settings = JSON.parse(localStorage.getItem("settings"));
        if(settings == null)
        {
            settings = {};
        }
//        var sl = settings.lines;
        var ind = -1;
        let item = settings.lines.find((o, i) => {

            if(o.id === parseInt(event.target.getAttribute("val")))
            {
                ind = i;
            }
        });

        settings.lines.splice(ind, 1);
        settings.lines_body.splice(ind, 1);
        

        localStorage.setItem('settings', JSON.stringify(settings));
        
        event.target.parentElement.parentElement.remove();
        
        
        var bb = video.parentElement.getBoundingClientRect();
        var canvas = document.getElementById("canvas");
        canvas.width = bb.width;
        canvas.height = bb.height;
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.restore();
        

                settings.lines.forEach(function(value, key) {
                   
                    console.log(key);
                    console.log(value);
                    
                    

                    // set line stroke and line width
                    if(value.type == "primary")
                    {
                        ctx.strokeStyle = 'red';
                    }
                    else if (value.type == "secondary")
                    {
                        ctx.strokeStyle = 'blue';
                    }
                    ctx.lineWidth = 5;

                    // draw a red line
                    ctx.beginPath();
                    ctx.moveTo(value.coords[0].x, value.coords[0].y);
                    ctx.lineTo(value.coords[1].x, value.coords[1].y);
                    ctx.stroke();
                    
                });
                canvas.style.zIndex = 10;
    }
    
    function to_video_time()
    {
//        console.log(event.target.innerHTML);
        document.getElementById("video");
        video.currentTime = event.target.innerHTML;
    }
    
    document.getElementById("video").addEventListener("click", setCoords);

    

    
     var inputNode = document.getElementById('inputVideo');
  inputNode.addEventListener('change', playSelectedFile, false);
  
  var button_set_numbers = document.getElementById('button_set_numbers');
  button_set_numbers.addEventListener('click', generateLines, false);
  
  var save_directions = document.getElementById('save_directions');
  save_directions.addEventListener('click', saveDirections, false);
  
  var save_vehicles = document.getElementById('save_vehicles');
  save_vehicles.addEventListener('click', saveVehicles, false);
  
  var save_keys = document.getElementById('save_keys');
  save_keys.addEventListener('click', saveKeys, false);
  
    var back_video = document.getElementById('back_video');
    back_video.addEventListener('click', backVideo, false);
  
    var to_directions = document.getElementById('to_directions');
    to_directions.addEventListener('click', toDirections, false);
    
    var back_directions = document.getElementById('back_to_directions');
    back_directions.addEventListener('click', backDirections, false);
    
    var back_vehicles = document.getElementById('back_to_vehicles');
    back_vehicles.addEventListener('click', backVehicles, false);
  
    var csv_results = document.getElementById('download_results');
    csv_results.addEventListener('click', csvResults, false);
  
    var begin_crossroads = document.getElementById('begin_crossroads');
    begin_crossroads.addEventListener('click', beginCrossroads, false);
  

    document.addEventListener('keydown', videoControl, false);
    
    var start_button = document.getElementById('start_button');
    start_button.addEventListener('click', beginCrossroads, false);
    
//    var close_video = document.getElementById('close_video');
//    close_video.addEventListener('click', hideStart, false);
    
    var to_load_settings = document.getElementById('to_load_settings');
    to_load_settings.addEventListener('click', toLoadSettings, false);
    
    var measurement1 = document.getElementById('measurement_1');
    measurement1.addEventListener('click', measurement_1, false);
    
    var measurement2 = document.getElementById('measurement_2');
    measurement2.addEventListener('click', measurement_2, false);
    
    var play_pause = document.getElementById('play_pause');
    play_pause.addEventListener('click', playPause, false);
    
     

  window.addEventListener("load", beginCrossroadsAuto, false);
  
  
    var inputImport = document.getElementById('inputImport');
  inputImport.addEventListener('change', import_file, false);
  
//  var startimport = document.getElementById("start_import");
//  startimport.addEventListener("click", start_import);
  
//    var remove_data = document.getElementById('remove_data');
//    remove_data.addEventListener('click', removeData, false);


