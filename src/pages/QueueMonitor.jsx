import { useEffect, useState } from "react";
import API from "@/config/api";

export default function QueueMonitor() {

    const [rows, setRows] = useState([]);

    async function load() {

        const response = await fetch(
             `${API}/job-queue.php`
        );

        const data = await response.json();

        setRows(data);
    }

    useEffect(() => {

        load();

        const timer = setInterval(
            load,
            2000
        );

        return () => clearInterval(timer);

    }, []);

    return (

        <div className="container">

            <h2>Queue Monitor</h2>

            <table>

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Trabajo</th>
                        <th>Estado</th>
                        <th>Worker</th>
                        <th>Intentos</th>
                        <th>Inicio</th>
                        <th>Fin</th>

                    </tr>

                </thead>

                <tbody>

                    {rows.map(row => (

                        <tr key={row.id}>

                            <td>{row.id}</td>
                            <td>{row.name}</td>
                            <td>{row.status}</td>
                            <td>{row.worker}</td>
                            <td>{row.attempts}</td>
                            <td>{row.started_at}</td>
                            <td>{row.finished_at}</td>

                        </tr>

                    ))}

                </tbody>

            </table>

        </div>

    );

}
