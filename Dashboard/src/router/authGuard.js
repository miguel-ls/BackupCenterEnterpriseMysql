export default function authGuard(to, from, next) {

    const user = localStorage.getItem("user");

    const token = localStorage.getItem("token");

    if (!user || !token) {

        next("/login");

        return;

    }

    next();

}