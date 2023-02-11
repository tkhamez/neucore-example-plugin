
// noinspection JSUnresolvedVariable

const pluginId = new URLSearchParams(window.location.search).get('id');

window.addEventListener("load", () => {

    // Get username of logged-in user.
    if (pluginId) {
        fetch('/plugin/' + pluginId + '/user').then(response => {
            response.json().then(data => {
                document.getElementById('userName').innerText = data.name + '!';
                if (data.authenticated) {
                    document.getElementById('content').style.display = 'block';
                }
            })
        });
    }

    // ESI requests
    document.getElementById('esiLink1').addEventListener('click', () => esiRequest(1));
    document.getElementById('esiLink2').addEventListener('click', () => esiRequest(2));
});

function esiRequest(num) {
    fetch('/plugin/' + pluginId + '/esi?num='+num).then(response => {
        response.json().then(data => {
            let text = data.error ? data.error : data.result;
            if (typeof text === typeof {}) {
                text = JSON.stringify(text);
            }
            document.getElementById('esiResponse'+num).innerText = text;
        })
    });
}