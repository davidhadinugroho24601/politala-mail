@if (!empty($getRecord()->pdf_path))
    <div style="position: relative;">
        <iframe id="googleDocFrame" src="{{ asset($getRecord()->pdf_path) }}" width="100%" height="600px" style="border: none;"></iframe>
        <button onclick="toggleFullScreen()" 
                style="position: absolute; top: 10px; right: 10px; background: #007bff; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 5px;">
            Fullscreen
        </button>
    </div>

    <script>
        function toggleFullScreen() {
            let iframe = document.getElementById("googleDocFrame");
            if (iframe.requestFullscreen) {
                iframe.requestFullscreen();
            } else if (iframe.mozRequestFullScreen) { // Firefox
                iframe.mozRequestFullScreen();
            } else if (iframe.webkitRequestFullscreen) { // Chrome, Safari and Opera
                iframe.webkitRequestFullscreen();
            } else if (iframe.msRequestFullscreen) { // IE/Edge
                iframe.msRequestFullscreen();
            }
        }
    </script>
@else
    <p>No document available.</p>
@endif
