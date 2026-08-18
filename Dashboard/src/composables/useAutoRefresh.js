import {
    onMounted,
    onUnmounted
} from "vue";

export function useAutoRefresh(callback, interval = 20000) {

    let timer = null;

    onMounted(() => {

        callback();

        timer = setInterval(callback, interval);

    });

    onUnmounted(() => {

        if (timer) {

            clearInterval(timer);

        }

    });

}