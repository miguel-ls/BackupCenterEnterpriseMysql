import API from "@/config/api";
import { token } from "@/api/auth";

async function request(endpoint, options = {}) {
    const response = await fetch(`${API}/${endpoint}`, {
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token()}`,
            ...options.headers
        },
        ...options
    });

    const content = await response.text();
    let json;

    try {
        json = content ? JSON.parse(content) : null;
    } catch {
        console.error(`Invalid JSON response from ${endpoint}:`, content);
        return {
            success: false,
            status: response.status,
            message: `Invalid JSON response from ${endpoint}`,
            raw: content
        };
    }

    if (!response.ok) {
        return {
            success: false,
            status: response.status,
            ...json,
            raw: content
        };
    }

    return json;
}

/* ================= DASHBOARD ================= */

export const getStatus = () => request("status.php");

export const getStatistics = () => request("statistics.php");

export const getVersion = () => request("version.php");

export const getChart = (
    clientId = 0,
    startDate = "",
    endDate = ""
) =>
    request(
        `chart.php?client_id=${clientId}&start_date=${startDate}&end_date=${endDate}`
    );

export const getGraphics = (filters = {}) => {
    const params = new URLSearchParams({
        client_id: String(filters.clientId ?? 0),
        status: filters.status ?? '',
        from: filters.from ?? '',
        to: filters.to ?? ''
    });

    return request(`graphics.php?${params.toString()}`);
};

export const getSystemStatus = () => request("system-status.php");

//export const getSystemInfo = () => request("system-info.php");

/* ================= SETTINGS ================= */

export const getSettings = () => request("settings.php");

export const updateSettings = (settings) =>
    request("settings.php", {
        method: "PUT",
        body: JSON.stringify(settings)
    });

export const testSftpGo = () =>
    request("sftpgo/test.php");
    
/* ================= QUEUE ================= */

export const getQueue = (filters = {}) => {
    const params = new URLSearchParams();

    if (filters.jobId) {
        params.set("job_id", String(filters.jobId));
    }

    if (filters.clientId) {
        params.set("client_id", String(filters.clientId));
    }

    if (filters.status) {
        params.set("status", filters.status);
    }

    if (filters.from) {
        params.set("from", filters.from);
    }

    if (filters.to) {
        params.set("to", filters.to);
    }

    const query = params.toString();

    return request(query ? `job-queue.php?${query}` : "job-queue.php");
};

export const completeQueue = (id) =>
    request("job-queue.php", {
        method: "POST",
        body: JSON.stringify({
            action: "complete",
            id
        })
    });

/* ================= HISTORY DASHBOARD ================= */

export const getHistoryDashboard = (
    page = 1,
    limit = 5,
    filters = {}
) => {
    const params = new URLSearchParams({
        page: String(page),
        limit: String(limit)
    });

    if (filters.clientId) {
        params.set("client_id", String(filters.clientId));
    }

    if (filters.jobId) {
        params.set("job_id", String(filters.jobId));
    }

    if (filters.status) {
        params.set("status", filters.status);
    }

    if (filters.from) {
        params.set("from", filters.from);
    }

    if (filters.to) {
        params.set("to", filters.to);
    }

    return request(`historyDashboard.php?${params.toString()}`);
};

/* ================= HISTORY ================= */

export const getHistory = (
    page = 1,
    limit = 5,
    filters = {}
) => {
    const params = new URLSearchParams({
        page: String(page),
        limit: String(limit)
    });

    if (filters.clientId) {
        params.set("client_id", String(filters.clientId));
    }

    if (filters.jobId) {
        params.set("job_id", String(filters.jobId));
    }

    if (filters.status) {
        params.set("status", filters.status);
    }

    if (filters.from) {
        params.set("from", filters.from);
    }

    if (filters.to) {
        params.set("to", filters.to);
    }

    return request(`history.php?${params.toString()}`);
};

/* ================= CLIENTS ================= */

export const getClients = () => request("clients.php");

/* ================= USERS ================= */

export const getUsers = () =>
    request("users.php");

export const createUser = (user) =>
    request("users.php", {
        method: "POST",
        body: JSON.stringify(user)
    });

export const updateUser = (user) =>
    request("users.php", {
        method: "PUT",
        body: JSON.stringify(user)
    });

export const deleteUser = (id) =>
    request("users.php", {
        method: "DELETE",
        body: JSON.stringify({ id })
    });

/* ================= AUDIT ================= */

export const getAudit = (page = 1, limit = 50, filters = {}) => {
    const params = new URLSearchParams({
        page: String(page),
        limit: String(limit)
    });

    if (filters.user) {
        params.set("user", filters.user);
    }

    if (filters.module) {
        params.set("module", filters.module);
    }

    if (filters.from) {
        params.set("from", filters.from);
    }

    if (filters.to) {
        params.set("to", filters.to);
    }

    if (filters.success !== "") {
        params.set("success", String(filters.success));
    }

    return request(`audit.php?${params.toString()}`);
};

/* ================= CONNECTIONS ================= */

export const getConnections = () => request("connections.php");

export const createConnection = (connection) =>
    request("connections.php", {
        method: "POST",
        body: JSON.stringify(connection)
    });

export const updateConnection = (connection) =>
    request("connections.php", {
        method: "PUT",
        body: JSON.stringify(connection)
    });

export const deleteConnection = (id) =>
    request("connections.php", {
        method: "DELETE",
        body: JSON.stringify({ id })
    });

export const updateConnectionInstallationStatus = (id, installed) =>
    request("connections.php", {
        method: "POST",
        body: JSON.stringify({
            action: "set-installed",
            id,
            installed
        })
    });

export const downloadInstallPackage = async (id) => {
    const response = await fetch(`${API}/connections.php`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token()}`
        },
        body: JSON.stringify({
            action: "install-package",
            id
        })
    });

    return response;
};

export const testConnection = (id) =>
    request("connections.php", {
        method: "POST",
        body: JSON.stringify({
            action: "test",
            id
        })
    });

/* ================= JOBS ================= */

export const getJobs = () => request("jobs.php");

export const createJob = (job) =>
    request("jobs.php", {
        method: "POST",
        body: JSON.stringify(job)
    });

export const updateJob = (job) =>
    request("jobs.php", {
        method: "PUT",
        body: JSON.stringify(job)
    });

export const deleteJob = (id) =>
    request("jobs.php", {
        method: "DELETE",
        body: JSON.stringify({ id })
    });

export const runJob = (id) =>
    request("jobs.php", {
        method: "POST",
        body: JSON.stringify({
            action: "run",
            id
        })
    });

export const toggleJob = (id, enabled) =>
    request("jobs.php", {
        method: "PATCH",
        body: JSON.stringify({
            id,
            enabled
        })
    });

/* ================= LOGS ================= */

export const getLogs = (
    page = 1,
    limit = 50
) =>
    request(`logs.php?page=${page}&limit=${limit}`);

/* ================= NOTIFICATIONS ================= */

export const getNotifications = () =>
    request("notifications.php");

export const getUpdates = () =>
    request("updates.php");

export const markNotificationsAsRead = () =>
    request("notifications.php", {
        method: "PUT"
    });

export const clearNotifications = () =>
    request("notifications.php", {
        method: "DELETE"
    });

/* ================= REPORTS ================= */

export const getReportSummary = () =>
    request("reports.php?action=summary");

export const getReportDaily = (days = 30) =>
    request(`reports.php?action=daily&days=${days}`);

export const getReportClients = () =>
    request("reports.php?action=clients");

export const getReportErrors = () =>
    request("reports.php?action=errors");

export const getReportJobs = () =>
    request("reports.php?action=jobs");

export const getReportConnections = () =>
    request("reports.php?action=connections");

/* ================= EXPORT ================= */

export const exportClientsExcel = () => {

    window.open(
        `${API}/reports-export.php?action=clients`,
        "_blank"
    );

};

export const exportJobsExcel = () => {

    window.open(
        `${API}/reports-export.php?action=jobs`,
        "_blank"
    );

};

export const exportConnectionsExcel = () => {

    window.open(
        `${API}/reports-export.php?action=connections`,
        "_blank"
    );

};

export const exportReport = async (action, type = "excel") => {

    const format = type === "pdf" ? "pdf" : "excel";

    // reports-export.php requires the Authorization header, so it can't be opened as a plain URL
    const response = await fetch(
        `${API}/reports-export.php?action=${encodeURIComponent(action)}&type=${format}`,
        {
            headers: {
                Authorization: `Bearer ${token()}`
            }
        }
    );

    if (!response.ok) {

        throw new Error(`Error al exportar el reporte (HTTP ${response.status})`);

    }

    const disposition = response.headers.get("Content-Disposition") ?? "";

    const match = disposition.match(/filename="?([^"]+)"?/);

    const filename = match
        ? match[1]
        : `reporte.${format === "pdf" ? "pdf" : "xlsx"}`;

    const blob = await response.blob();

    const url = window.URL.createObjectURL(blob);

    const link = document.createElement("a");

    link.href = url;
    link.download = filename;

    document.body.appendChild(link);
    link.click();
    link.remove();

    window.URL.revokeObjectURL(url);

};

/*
|--------------------------------------------------------------------------
| CLIENTS
|--------------------------------------------------------------------------
*/

export const createClient = (client) =>
    request("clients.php", {
        method: "POST",
        body: JSON.stringify(client)
    });

export const updateClient = (client) =>
    request("clients.php", {
        method: "PUT",
        body: JSON.stringify(client)
    });

export const deleteClient = (id) =>
    request("clients.php", {
        method: "DELETE",
        body: JSON.stringify({ id })
    });

export const resetClientPassword = (id) =>
    request("clients.php", {
        method: "POST",
        body: JSON.stringify({
            action: "reset-password",
            id
        })
    });    
