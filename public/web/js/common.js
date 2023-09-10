//const urlApi = "http://localhost/gestion-stock/public/api/";
const urlApi = "https://paraeljinene.tn/api/";

const codePromoUrl = "code_promos";
const clientUrl = '/api/clients/';
const sendHttpRequest = (method,url,data) => {
    const promise = new Promise((resolve,reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.responseType = 'json';
        if(data){
            xhr.setRequestHeader('Content-Type','application/json');
        }

        xhr.onload = () => {
            if(xhr.status >= 400) {
                reject(xhr.response);
            }else{
                resolve(xhr.response);
            }
        }

        xhr.onerror = () => {
            reject('Something went wrong');
        };

        xhr.send(JSON.stringify(data));
    });
    return promise;
}

const numberWithSpaces = (x) => {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

const clearUrlFromParams = () => {
    let href = window.location.href;
    window.history.replaceState({}, document.title + Math.random(), "/" + href.substring(href.indexOf("gestion-stock")).split('?')[0]);
}