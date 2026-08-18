export function token(){

    return localStorage.getItem("token") ?? "";

}

export async function api(url,options={}){

    options.headers ??={};

    options.headers.Authorization="Bearer "+token();

    return fetch(

        url,

        options

    );

}