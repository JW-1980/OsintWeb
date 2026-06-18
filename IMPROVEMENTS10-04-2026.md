# Software Improvements List

## 100 General Improvements

1. [Better looking / UI improvements] Dedicated "dark mode" toggle for the UI with high-contrast options.
2. [Better user experience] Keyboard shortcuts for map navigation (pan, zoom, reset).
3. [Easier to use] Drag and drop file uploads for evidence in all forms.
4. [Additional automation] Webhook integrations for alerts to Discord.
5. [Additional automation] Webhook integrations for alerts to Slack.
6. [Additional automation] Webhook integrations for alerts to MS Teams.
7. [Free ways of gathering more useful and relevant data] RSS feeds for verified events per geographic zone.
8. [Better user experience] User defined custom map markers with customizable icons and colors.
9. [Improved security] Two-factor authentication (2FA) via TOTP for all user accounts.
10. [Improved security] Passkey authentication support (WebAuthn).
11. [Improved security] Content Security Policy (CSP) headers hardening.
12. [Easier to use] Bulk import of intelligence tips via CSV.
13. [CRUD where possible] Export of case management workspaces to ZIP archive.
14. [Better user experience] Auto-save functionality for reports being drafted.
15. [Better user experience] Autosave for case management workspaces.
16. [Free ways of gathering more useful and relevant data] Integration with public weather APIs (like Open-Meteo) for historical weather data overlay.
17. [Display and gathering of interesting or useful statistics] Activity heatmap for user contributions on profile pages.
18. [Features that encourage and allow more interaction] Leaderboard for top trusted tip contributors.
19. [Better looking / UI improvements] High contrast accessibility mode for visually impaired users.
20. [Better user experience] Keyboard accessibility (WCAG compliance) improvements for all forms.
21. [Better user experience] Screen reader optimization for all map elements.
22. [Additional automation] Automatic EXIF metadata extraction and display for uploaded images.
23. [Improved PII and other data leakage prevention or handling] Automatic stripping of EXIF data on public facing images.
24. [Improved PII and other data leakage prevention or handling] Automatic watermarking of exported PDF reports.
25. [Additional automation] Automatic daily database backups to external storage.
26. [Improved security] Rate limiting per IP for public API endpoints.
27. [Improved security] CAPTCHA integration on the public tip submission form.
28. [Better looking / UI improvements] Customizable dashboard widgets for the user home page.
29. [Easier to use] Pinned cases in the case management workspace for quick access.
30. [CRUD where possible] Tagging system for individual pieces of evidence.
31. [Improved performance] Advanced caching of map tiles for offline mode using Service Workers.
32. [Free ways of gathering more useful and relevant data] Integration with Wikipedia API to fetch summaries of conflict zones.
33. [Additional automation] Automated broken link checker for external references.
34. [Additional automation] Integration with Internet Archive Wayback Machine to automatically submit new URLs.
35. [Additional automation] Integration with archive.today to save URLs automatically.
36. [Easier to use] Customizable email digest frequency (daily, weekly) for system alerts.
37. [CRUD where possible] Full CRUD for custom entity types in network analysis.
38. [Improved security] User role delegation for temporary access.
39. [Improved security] Audit log export to CSV for compliance purposes.
40. [Improved security] Configurable session timeout for security.
41. [Improved security] IP whitelisting for admin access.
42. [Improved PII and other data leakage prevention or handling] Option to blur sensitive imagery by default until clicked.
43. [Creating standardized components] Standardized Vue component for timeline rendering.
44. [Creating standardized components] Standardized Vue component for displaying equipment loss statistics.
45. [Creating standardized components] Standardized data table component with built-in sorting and pagination.
46. [Better user experience] In-app notification center for system alerts.
47. [Features that encourage and allow more interaction] User mention functionality (@username) in case workspaces.
48. [Features that encourage and allow more interaction] Threaded comments on evidence items.
49. [Features that encourage and allow more interaction] User profiles with public contribution history.
50. [Telemetry collection] System health monitoring dashboard (CPU, memory, database size).
51. [Telemetry collection] Error rate telemetry collection frontend logger.
52. [Easier to use] Custom defined geofences for alerts using polygon drawing.
53. [Easier to use] Measurement tools (distance, area) directly on the map.
54. [CRUD where possible] Export map view to high-resolution image.
55. [CRUD where possible] Import GeoJSON files to display on the map.
56. [CRUD where possible] Export territorial control zones to KML format for Google Earth.
57. [CRUD where possible] Version history for reports.
58. [Improved security] Option to lock cases to prevent further edits.
59. [Improved security] Password complexity requirements configuration in admin panel.
60. [Improved security] Integration with HaveIBeenPwned API to check for compromised user passwords.
61. [Improved security] Displaying active sessions per user with option to revoke.
62. [Features that encourage and allow more interaction] "Share to social media" buttons for public verified events.
63. [Features that encourage and allow more interaction] Embeddable map widgets for third-party websites.
64. [Improved security] Public API key management UI with usage tracking.
65. [Better user experience] Markdown support in all text descriptions.
66. [Better user experience] Preview mode for markdown rendering.
67. [Creating standardized components] Standardized form validation rules extracted into reusable composables.
68. [Better looking / UI improvements] User customizable color schemes for map zones.
69. [Easier to use] Option to group timeline events by month/year.
70. [Easier to use] Comparison view for two different map dates side-by-side.
71. [Better user experience] Interactive tutorial for new users (onboarding tour).
72. [Better user experience] Tooltips on complex UI elements.
73. [Easier to use] Context menus (right-click) on the map for quick actions.
74. [CRUD where possible] Bulk deletion of unverified tips.
75. [Easier to use] Bulk assignment of tips to moderators.
76. [Features that encourage and allow more interaction] Real-time presence indicators in case workspaces.
77. [Additional automation] Offline queue for actions performed without internet, synced when back online.
78. [CRUD where possible] Custom metadata fields for events (key-value pairs).
79. [Easier to use] Search by exact geographic coordinates (Lat/Lon or MGRS).
80. [Easier to use] Coordinate conversion tool (Lat/Lon to MGRS).
81. [Free ways of gathering more useful and relevant data] Integration with OpenStreetMap Nominatim for reverse geocoding.
82. [Additional automation] Auto-population of country/region based on coordinates.
83. [Display and gathering of interesting or useful statistics] Detailed statistics on equipment losses by country of origin.
84. [Display and gathering of interesting or useful statistics] Graph of reporting volume over time.
85. [Improved PII and other data leakage prevention or handling] Regex-based redacting of emails and phone numbers in public text.
86. [Telemetry collection] Telemetry on page load times and API response times.
87. [Features that encourage and allow more interaction] User feedback form on every page.
88. [CRUD where possible] CRUD for "sources" trust levels.
89. [Additional automation] Automatic detection of duplicate image uploads via perceptual hashing (phash).
90. [CRUD where possible] Option to merge duplicate events.
91. [Easier to use] Hierarchical organization of actors (groups, subgroups, individuals).
92. [Better looking / UI improvements] Visual indicator for evidence quality (e.g., star rating).
93. [Easier to use] Sortable lists for all data tables by any column.
94. [Improved PII and other data leakage prevention or handling] Export all user data (GDPR requirement).
95. [Improved PII and other data leakage prevention or handling] Account deletion with grace period (GDPR requirement).
96. [Improved PII and other data leakage prevention or handling] Customizable privacy policy and terms of service pages.
97. [Better looking / UI improvements] Displaying the "last updated" timestamp prominently on all entities.
98. [Display and gathering of interesting or useful statistics] Display most active regions by event count.
99. [Additional automation] Auto-close stale tips after a configured time period.
100. [Creating standardized components] Reusable modal component for confirmations across the application.

## 15 Installation & Hosting Improvements

1. Docker Compose setup for easy local deployment.
2. Helm chart for Kubernetes deployment.
3. Ansible playbook for automated server provisioning.
4. Terraform scripts for AWS infrastructure deployment.
5. Terraform scripts for DigitalOcean infrastructure deployment.
6. Interactive CLI setup wizard for environment variables.
7. Pre-configured GitHub Actions workflow for CI/CD.
8. Pre-configured GitLab CI pipeline.
9. Nginx reverse proxy configuration template.
10. Systemd service file template for queue workers.
11. Script to automatically configure Let's Encrypt SSL.
12. Pre-built Docker images hosted on Docker Hub or GHCR.
13. Healthcheck endpoint specifically for container orchestration.
14. Automatic database migration run on container startup script.
15. Environment variable validation on application boot.

## 20 Redesigned Pages

1. Redesign Dashboard Page. Benefit: Improved layout for quicker at-a-glance metrics and better mobile responsiveness.
2. Redesign Event Creation Page. Benefit: Streamlined form layout with contextual help, reducing cognitive load and time to submit.
3. Redesign Map View Page. Benefit: Maximized map area with collapsible side panels for tools, improving situational awareness.
4. Redesign Timeline View. Benefit: Better visual separation of overlapping events and smoother scrolling performance.
5. Redesign Case Management Workspace. Benefit: Kanban-style board layout for better organization of tasks and evidence.
6. Redesign Source Verification Page. Benefit: Clearer visual hierarchy for trust scores and cross-referencing data.
7. Redesign User Profile Page. Benefit: Unified view of activity history and preferences in a tabbed interface.
8. Redesign Admin Settings Page. Benefit: Categorized settings with search functionality to find specific configurations quickly.
9. Redesign Tip Submission Form (Public). Benefit: Step-by-step wizard style to guide users and improve data quality.
10. Redesign Network Analysis Graph. Benefit: Enhanced interactivity with node grouping and better physics engine for layout.
11. Redesign Equipment Loss Tracker. Benefit: Card-based layout with prominent imagery and progress bars for verified losses.
12. Redesign Login/Registration Page. Benefit: Modern split-screen design with consistent branding and clearer error states.
13. Redesign Report Generator Page. Benefit: WYSIWYG editor integration with live preview of the final document.
14. Redesign Alerts Management Page. Benefit: Rule builder interface with logical operators for complex alert conditions.
15. Redesign Social Media Monitoring Feed. Benefit: Masonry layout for better utilization of screen space with varying content sizes.
16. Redesign User Roles & Permissions Matrix. Benefit: Grid-based toggles for easier visualization of complex permission assignments.
17. Redesign Audit Log Viewer. Benefit: Advanced filtering sidebar and collapsible row details to handle large volumes of logs.
18. Redesign Geolocation Verification Tool. Benefit: Split-screen view for side-by-side comparison of evidence and reference imagery.
19. Redesign Disinformation Pattern Dashboard. Benefit: Heatmaps and trending charts to quickly identify coordinated campaigns.
20. Redesign Offline Sync Status Page. Benefit: Clear progress indicators and conflict resolution UI to manage offline data merging.
