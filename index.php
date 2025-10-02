<?php
require_once "utils.php";
session_start();

loginSecurity();
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/x-icon" href="images/logo.webp" />
        <link rel="stylesheet" href="css/style_index.css" />
        <title>NextStep</title>
    </head>
    <body>
        <div class="navbar">
            <div class="brand-name">NextStep</div>
            <div class="nav-buttons">
                <button onclick="window.location.href='settings.php'">Settings</button>
                <button onclick="window.location.href='map.php'">Map</button>
                <button onclick="window.location.href='logout.php'">Log out</button>
            </div>
        </div>

        <div class="action-buttons">
            <button class="action-btn" onclick="openSearchModal()">
                🔍 Search & Filter
            </button>
            <div class="workflow-indicator">→</div>
            <button
                class="action-btn"
                id="composeBtn"
                onclick="openEmailModal()"
                disabled
            >
                ✉️ Compose Email (<span id="selectedCount">0</span> selected)
            </button>
        </div>

        <div class="content-container">
            <div class="table-section">
                <div class="table-header">
                    <div class="selected-info">
                        <span id="totalCount">10</span> records |
                        <span id="selectedCountText">0</span> selected
                    </div>
                    <div class="select-all-container">
                        <input
                            type="checkbox"
                            id="selectAll"
                            onchange="toggleSelectAll()"
                        />
                        <label for="selectAll">Select All</label>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">John Doe</td>
                                <td data-label="Email">john@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-active"
                                        >Active</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-20</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Jane Smith</td>
                                <td data-label="Email">jane@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-pending"
                                        >Pending</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-19</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Bob Johnson</td>
                                <td data-label="Email">bob@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-active"
                                        >Active</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-18</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Alice Williams</td>
                                <td data-label="Email">alice@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-inactive"
                                        >Inactive</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-17</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Charlie Brown</td>
                                <td data-label="Email">charlie@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-active"
                                        >Active</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-16</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Diana Prince</td>
                                <td data-label="Email">diana@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-pending"
                                        >Pending</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-15</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Frank Miller</td>
                                <td data-label="Email">frank@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-active"
                                        >Active</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-14</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Grace Lee</td>
                                <td data-label="Email">grace@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-inactive"
                                        >Inactive</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-13</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Mike Wilson</td>
                                <td data-label="Email">mike@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-active"
                                        >Active</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-12</td>
                            </tr>
                            <tr>
                                <td data-label="Select">
                                    <input
                                        type="checkbox"
                                        onchange="updateSelection()"
                                    />
                                </td>
                                <td data-label="Name">Sarah Connor</td>
                                <td data-label="Email">sarah@example.com</td>
                                <td data-label="Status">
                                    <span class="status-badge status-pending"
                                        >Pending</span
                                    >
                                </td>
                                <td data-label="Date">2025-09-11</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Search Modal -->
        <div class="modal" id="searchModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Search & Filter Records</h2>
                    <button class="close-btn" onclick="closeSearchModal()">
                        &times;
                    </button>
                </div>
                <form class="search-form" id="searchForm">
                    <div class="form-group">
                        <label for="searchQuery">Search:</label>
                        <input
                            type="text"
                            id="searchQuery"
                            placeholder="Search by name or email..."
                        />
                    </div>
                    <div class="search-row">
                        <div class="form-group">
                            <label for="statusFilter">Status:</label>
                            <select id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active Users</option>
                                <option value="pending">Pending Users</option>
                                <option value="inactive">Inactive Users</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sortBy">Sort By:</label>
                            <select id="sortBy">
                                <option value="date">
                                    Date (Latest First)
                                </option>
                                <option value="name">Name (A-Z)</option>
                                <option value="email">Email (A-Z)</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            onclick="clearFilters()"
                        >
                            Clear Filters
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" id="emailModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Compose Email to Selected Recipients</h2>
                    <button class="close-btn" onclick="closeEmailModal()">
                        &times;
                    </button>
                </div>
                <div class="recipients-info">
                    <h4>Email will be sent to:</h4>
                    <div class="recipients-list" id="recipientsList">
                        No recipients selected
                    </div>
                </div>
                <form id="emailForm">
                    <div class="form-group">
                        <label for="emailSubject">Subject:</label>
                        <input
                            type="text"
                            id="emailSubject"
                            placeholder="Enter email subject"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label for="emailMessage">Message:</label>
                        <textarea
                            id="emailMessage"
                            placeholder="Compose your email message here..."
                            required
                        ></textarea>
                    </div>
                    <div class="modal-actions">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            onclick="closeEmailModal()"
                        >
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Send to <span id="sendCount">0</span> Recipients
                        </button>
                    </div>
                </form>
            </div>
        </div>






 ?>
 ?>
 ?>
 ?>
 ?>
 ?>
 ?>