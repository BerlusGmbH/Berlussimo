import IO from "socket.io-client";
import Echo from "laravel-echo";

export default (new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname,
    client: IO
}) as any);