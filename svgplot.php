<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache">
    <title>SVGplot</title>
    <style>
        .greek { text-decoration:none;font-size:14pt;color:blue; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script>
        function insertAtCursor(myField, myValue) {
            if (document.selection) {
                myField.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
            } else if (myField.selectionStart || myField.selectionStart == '0') {
                myField.focus();
                var startPos = myField.selectionStart;
                var endPos = myField.selectionEnd;
                myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
                myField.setSelectionRange(endPos + myValue.length, endPos + myValue.length);
            } else {
                myField.value += myValue;
            }
        }

        function greekkbcontrol(obj) {
            var curleft = 0;
            var curtop = 0;
            if (obj.offsetParent) {
                do {
                    curleft += obj.offsetLeft;
                    curtop += obj.offsetTop;
                } while (obj = obj.offsetParent);
            }
            document.getElementById('greekkb').style.left = curleft + "px";
            document.getElementById('greekkb').style.top = (curtop + 22) + "px";
            document.getElementById('greekkb').style.visibility = (document.getElementById('greekkb').style.visibility == 'visible') ? 'hidden' : 'visible';
        }

        function greekclicked(val) {
            insertAtCursor(document.getElementById('str'), val);
            document.getElementById('greekkb').style.visibility = 'hidden';
        }

        function submitForm(event) {
            event.preventDefault();
            var form = document.getElementById('svgForm');
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open("POST", form.action + '?t=' + new Date().getTime(), true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('resultFrame').innerHTML = xhr.responseText;
                } else {
                    console.error('An error occurred!');
                }
            };
            xhr.send(formData);
        }


        function copySvgAsPng() {
            var svgElement = document.querySelector('#resultFrame svg');
            if (!svgElement) {
                alert('No SVG found to copy!');
                return;
            }
        
            var svgData = new XMLSerializer().serializeToString(svgElement);
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
        
            var svgSize = svgElement.getBoundingClientRect();
            canvas.width = svgSize.width;
            canvas.height = svgSize.height;
        
            var img = new Image();
            img.onload = function () {
                ctx.drawImage(img, 0, 0);
                canvas.toBlob(function (blob) {
                    navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]).then(function() {
                        alert('Image copied to clipboard!');
                    }, function(error) {
                        alert('Failed to copy image: ' + error);
                    });
                });
            };
        
            img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
        }

        function saveSvgAsPng() {
            var svgElement = document.querySelector('#resultFrame svg');
            if (!svgElement) {
                alert('No SVG found to save!');
                return;
            }

            var svgData = new XMLSerializer().serializeToString(svgElement);
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');

            var svgSize = svgElement.getBoundingClientRect();
            canvas.width = svgSize.width;
            canvas.height = svgSize.height;

            var img = new Image();
            img.onload = function () {
                ctx.drawImage(img, 0, 0);
                canvas.toBlob(function (blob) {
                    saveAs(blob, 'svgplot.png');
                });
            };

            img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
        }

    </script>
</head>
<body>
    <h2>SVGplot 1.2</h2>
    Created by Harry Lau, 2010-2024<br><br>

    <select name="ComboFunction" onChange="insertAtCursor(document.getElementById('str'), this.options[this.selectedIndex].value); this.selectedIndex=0;">
        <option value="">[FUNCTION]</option>
        <option value="line[black]=">line</option>
        <option value="dash[black]=">dash</option>
        <option value="curve[black]=">curve</option>
        <option value="points[black]=">points</option>
        <option value="mark[black,0,0]=x,y,text">mark</option>
        <option value="equation[black]=">equation f(x)</option>
    </select>

    <select name="ComboFormat" onChange="insertAtCursor(document.getElementById('str'), this.options[this.selectedIndex].value); this.selectedIndex=0;">
        <option value="">[FORMAT]</option>
        <option value="\nolabels">\nolabels</option>
        <option value="\nolabelnums">\nolabelnums</option>
        <option value="\noaxes">\noaxes</option>
        <option value="\nogrids">\nogrids</option>
        <option value="\sideaxes">\sideaxes</option>
    </select>

    <select name="ComboTextstyle" onChange="insertAtCursor(document.getElementById('str'), this.options[this.selectedIndex].value); this.selectedIndex=0;">
        <option value="">[TEXT-STYLE]</option>
        <option value="\it{}">italic</option>
        <option value="^{}">superscript</option>
        <option value="_{}">subscript</option>
    </select>

    <select name="ComboSymbol" onChange="insertAtCursor(document.getElementById('str'), this.options[this.selectedIndex].value); this.selectedIndex=0;">
        <option value="">[SYMBOL]</option>
        <option value="°">degree °</option>
        <option value="£">pound £</option>
        <option value="×">times ×</option>
        <option value="±">plus/minus ±</option>
        <option value="½">half ½</option>
        <option value="≈">approximate ≈</option>
    </select>

    <input type="button" value="Greek keyboard" onclick="greekkbcontrol(this)">
    <div id="greekkb" name="greekkb" style="position:absolute;visibility:hidden;left:400px;top:100px;width:220px;background-color:#BBBBFF;opacity:0.7;filter:alpha(opacity=70);">
        <table width="220" border="1" cellspacing="0">
            <?php
            $greek[0] = array('α', 'β', 'γ', 'δ', 'ε', 'ϵ', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'ς', 'τ', 'υ', 'φ', 'ϕ', 'χ', 'ψ', 'ω');
            $greek[1] = array('Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω');

            for ($i = 0; $i <= 1; $i++) {
                for ($j = 0; $j < count($greek[$i]); $j++) {
                    echo '<td width="10%" align="center"><a class="greek" href="#" onclick="greekclicked(\'' . $greek[$i][$j] . '\')">' . $greek[$i][$j] . "</a></td>";
                    if ($j % 10 == 9) echo '<tr>';
                }
                echo '<tr>';
            }
            ?>
        </table>
    </div>

    &nbsp; &nbsp; | &nbsp;
    <a href="#" onclick='window.open("help.htm", "helpWindow", "status=1, height=360, width=480, resizable=1, scrollbars=1")'>HELP</a>

    <form id="svgForm" action="svgplotwrite.php" method="POST" onsubmit="submitForm(event)">
        <textarea id="str" name="str" rows="10" cols="80">
xlabel=\it{t} / s
xparam=0,250,25,1
ylabel=\it{T} / °C
yparam=0,80,10
subdiv=5
gridsize=1.5
\sideaxes

points=0,32;50,39;90,46;150,54;180,60;220,66;250,72;
line[red]=0,32;275,75
        </textarea><br>
        <input type="submit" value="Submit">
    </form>

    <div id="resultFrame" style="width: 100%; height: 100%;"></div>
    <button onclick="copySvgAsPng()">Copy to Clipboard</button>
    <button onclick="saveSvgAsPng()">Save as PNG</button>
</body>
</html>
