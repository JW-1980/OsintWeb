# OsintWeb Development To-Do List

This document tracks all features and capabilities mentioned in the README.md, organized by category.

---

## Intelligence & Verification Systems

### Source Verification
- [ ] **Source Verification System** - Automatically grade the reliability of sources and cross-reference new reports against known trusted outlets
- [ ] **Data Quality & Confidence Scoring** - Assign confidence scores to every data point based on number and quality of sources
- [ ] **Collaborative Verification Workflow** - Multi-stage approval process where community submissions are vetted by moderators before publication

### Geolocation & Attribution
- [ ] **Geolocation Verification Tools** - Precise tools for matching ground-level footage with satellite imagery to confirm exact coordinates
- [ ] **Attribution & Chronolocation** - Tools to determine not just where, but exactly when an event occurred using shadow analysis and weather data
- [ ] **Weather & Environmental Data** - Overlay historical weather data to verify claims about when footage was taken

### Evidence & Forensics
- [ ] **Evidence Preservation** - Automatically archive web pages and media to IPFS or local storage to prevent link rot
- [ ] **Video Frame Analysis** - Extract metadata and keyframes from video evidence for detailed forensic examination
- [ ] **Reverse Image Search Aggregator** - Simultaneously search multiple engines to detect reused or manipulated imagery
- [ ] **Audio Analysis & Authentication** - Tools to visualize spectrograms and detect editing in released audio recordings
- [ ] **Cross-Platform Correlation** - Link disparate pieces of evidence across different platforms to build a stronger case

---

## Data Collection & Monitoring

### Social Media & Web
- [ ] **Social Media Monitoring** - Track and archive posts from key accounts across major platforms to preserve fleeting evidence
- [ ] **Dark Web Monitoring** - Specialized scrapers for monitoring discussions on non-indexed networks
- [ ] **Disinformation Pattern Detection** - Algorithms that flag coordinated inauthentic behavior and potential propaganda campaigns

### Signal Intelligence
- [ ] **Flight Tracking Integration (ADS-B)** - Ingest real-time flight data to correlate air movements with ground events
- [ ] **Maritime Vessel Tracking (AIS)** - Monitor naval positioning and shipping lanes to detect blockades or supply runs
- [ ] **Radio Frequency Signal Monitoring** - (Experimental) Interface for logging and visualizing RF spectrum data reports

### Document Processing
- [ ] **OCR & Document Analysis** - Extract text from scanned documents and images to make them searchable
- [ ] **Multi-Language Translation Engine** - Auto-translate reports and social media posts from over 100 languages

---

## Identification & Recognition

- [ ] **Facial Recognition Assistant** - Assist in identifying key figures in footage against a known database of actors
- [ ] **Vehicle Identification Database** - A reference library of military vehicle silhouettes and markings to aid identification

---

## Analysis & Visualization

### Network Analysis
- [ ] **Network Analysis** - Visualize relationships between actors, funders, and proxy groups to understand the hidden web of influence
- [ ] **Communication Network Mapping** - Visualize the flow of information and command structures between entities

### Timeline & Temporal
- [ ] **Chronological Timeline Builder** - A linear view of events that helps establish causality and sequence
- [ ] **Historical Playback** - Animate map changes over time to visualize conflict progression
- [ ] **Diff Views** - Compare map states between any two dates
- [ ] **Custom Investigation Timelines** - Construct custom timelines linking disparate events into cohesive narratives

### Supply Chain
- [ ] **Supply Chain Disruption Tracking** - Specific modules to analyze how conflict impacts logistics and critical infrastructure

---

## Interactive Mapping

### Core Mapping Features
- [ ] **Control Zones** - Draw and color-code control zones with faction assignments to track territorial changes
- [ ] **Dynamic Legend** - Automatically generated legend based on visible map elements
- [ ] **Satellite Layers** - Toggle between OpenStreetMap, Satellite, and hybrid views

### Satellite Integration
- [ ] **Satellite Imagery Integration** - Overlay high-resolution satellite layers directly onto the operational map for context

### Export Options
- [ ] **Map Export PNG** - Export maps as PNG format
- [ ] **Map Export JPG** - Export maps as JPG format
- [ ] **Map Export PDF** - Export maps as PDF format
- [ ] **Map Export SVG** - Export maps as SVG format
- [ ] **High-Resolution Export** - Support up to 300 DPI for professional publications

---

## Public Intelligence Portal

- [ ] **No Login Required Browsing** - Allow browsing the interactive map, events, equipment, and conflicts without authentication
- [ ] **Public Events Page** - Dedicated public page for exploring events
- [ ] **Public Equipment Page** - Dedicated public page for exploring equipment
- [ ] **Public Actors Page** - Dedicated public page for exploring actors
- [ ] **Public Conflict Zones Page** - Dedicated public page for exploring conflict zones
- [ ] **Anonymous Tips Submission** - Submit intelligence tips through a streamlined public form
- [ ] **Contact Page** - Professional contact page for community engagement
- [ ] **About Page** - Professional about page for platform information

---

## Military Equipment Database

### Equipment Catalog
- [ ] **190+ Equipment Profiles** - Pre-loaded items including ships, tanks, aircraft, helicopters, drones
- [ ] **50+ Missile Systems** - Detailed missile system profiles
- [ ] **Equipment Specifications** - Track dimensions, armament, range, and production numbers
- [ ] **Equipment CRUD Operations** - Full management capabilities via API and admin panel

### Inventory & Losses
- [ ] **Equipment Loss Tracking** - Maintain a database of verified vehicle and equipment losses, categorized by type and model (Oryx-style)
- [ ] **Per-Country Inventory Tracking** - Monitor inventory levels for every country
- [ ] **Loss Statistics** - Track and display verified losses

---

## Event Documentation

### Event Types & Templates
- [ ] **49+ Event Type Templates** - Covering airstrikes, naval engagements, cyber attacks, humanitarian crises, etc.
- [ ] **Combat Event Templates** - Templates for combat-related events
- [ ] **Sighting Event Templates** - Templates for equipment/personnel sightings
- [ ] **Infrastructure Damage Templates** - Templates for infrastructure events

### Event Attribution & Evidence
- [ ] **Event Attribution** - Link events to specific perpetrators, victims, and equipment owners
- [ ] **Evidence Attachments** - Attach images, videos, and documents to verify events
- [ ] **Equipment Loss Linking** - Link specific equipment losses to events

---

## Actor Attribution

### State Actors
- [ ] **197 Country Support** - Full support for all state actors
- [ ] **Country Profiles** - Detailed profiles for each country

### Non-State Actors
- [ ] **Militia Tracking** - Track militia groups and their activities
- [ ] **PMC Tracking** - Track private military companies (e.g., Wagner)
- [ ] **Terrorist Organization Tracking** - Track terrorist organizations
- [ ] **Proxy Group Attribution** - Understand alliances and proxy relationships

### Search & Selection
- [ ] **Smart Autocomplete** - Priority-based sorting for active conflict parties
- [ ] **Aliases Support** - Support for alternate names
- [ ] **Fuzzy Search** - Real-time fuzzy search across actors

---

## Role-Based Access Control (RBAC)

### Permissions System
- [ ] **50+ Permission Types** - Fine-grained access control permissions
- [ ] **Admin Permissions UI** - Full CRUD interface for permissions management
- [ ] **Role Permissions UI** - Full CRUD interface for roles management

### Role Hierarchy
- [ ] **Admin Role** - Full system access
- [ ] **Moderator Role** - Content moderation capabilities
- [ ] **Analyst Role** - Analysis and investigation tools
- [ ] **Viewer Role** - Read-only access
- [ ] **Role Inheritance** - Hierarchical permission inheritance

### Team Management
- [ ] **Group-Based Permissions** - Assign permissions to groups
- [ ] **Time-Limited Access Grants** - Temporary permission assignments

---

## Alert & Notification System

### Alert Configuration
- [ ] **Region-Based Alerts** - Set alerts for specific geographic areas
- [ ] **Keyword Alerts** - Set alerts for specific keywords
- [ ] **Event Type Alerts** - Set alerts for specific event types
- [ ] **Actor-Based Alerts** - Set alerts for specific actors

### Notification Delivery
- [ ] **Real-time Notifications** - Instant updates when matching events are created
- [ ] **Email Notifications** - Alert delivery via email
- [ ] **Push Notifications** - Alert delivery via push notifications
- [ ] **Alert History** - Track triggered alerts with links to relevant events

---

## Report Generation

### SITREP Builder
- [ ] **Situation Report Templates** - Customizable SITREP templates
- [ ] **PDF Export** - Export reports as PDF
- [ ] **DOCX Export** - Export reports as Word documents
- [ ] **HTML Export** - Export reports as HTML

### Automation & Branding
- [ ] **Daily Scheduled Reports** - Automated daily report generation
- [ ] **Weekly Scheduled Reports** - Automated weekly report generation
- [ ] **Monthly Scheduled Reports** - Automated monthly report generation
- [ ] **Custom Organization Logos** - Add organization branding to reports
- [ ] **Custom Report Styling** - Customize report appearance

---

## News & CMS

### Publishing System
- [ ] **Rich Text Editor** - Create analysis, reports, and tutorials with rich text editing
- [ ] **Article Publishing** - Publish news articles and analysis
- [ ] **Tutorial Publishing** - Publish how-to guides and tutorials

### Monetization & SEO
- [ ] **Premium Content Gates** - Subscription-based access barriers for monetization
- [ ] **Meta Tags Support** - SEO meta tags for articles
- [ ] **Custom Slugs** - SEO-friendly URL slugs
- [ ] **Social Sharing** - Built-in social media sharing support

---

## Community & Engagement

### Comments System
- [ ] **Threaded Comments** - Deep nesting support for discussions
- [ ] **Comment Moderation Tools** - Moderation capabilities for comments

### Anti-Spam & Security
- [ ] **Honeypot Protection** - Hidden fields to catch bots
- [ ] **Rate Limiting** - Prevent abuse through rate limits
- [ ] **Spam Scoring** - Score and filter spam submissions

### User Profiles
- [ ] **Customizable Profiles** - User profile customization
- [ ] **Generated Avatars** - Unique automatically generated avatars

---

## AI & Automation

### AI Agents
- [ ] **Geolocation AI Agent** - AI-powered geolocation assistance
- [ ] **Image Verification AI Agent** - AI-powered image verification
- [ ] **Agent Management Admin UI** - Create, configure, and monitor AI agents

### Skills System
- [ ] **Keyword-Triggered Skills** - Automated capabilities triggered by keywords
- [ ] **Skills Administration** - Manage skills with triggers, configurations, and agent assignments

### Transcription
- [ ] **AI Audio Transcription** - AI-powered audio transcription
- [ ] **Speaker Identification** - Identify speakers in audio via OpenRouter

---

## Achievement System

### Achievement Management
- [ ] **Achievement CRUD** - Admin interface for creating/managing achievements
- [ ] **Achievement Categories** - Organize by contribution, verification, engagement types
- [ ] **Rarity Tiers** - Common to legendary achievement rarity

### Points & Rewards
- [ ] **Configurable Point Values** - Customize point values for achievements
- [ ] **User Achievement Tracking** - Track user achievements
- [ ] **Profile Achievement Display** - Display achievements on user profiles

---

## Privacy & Security

### GDPR Compliance
- [ ] **Consent Management** - Track and manage user consent
- [ ] **Data Export** - Allow users to export their data
- [ ] **Right to be Forgotten** - Allow users to delete all their data
- [ ] **Data Retention Policies** - Define and enforce retention periods

### Audit & Logging
- [ ] **Cryptographic Audit Trail** - Tamper-evident logging of all changes
- [ ] **Action Logging** - Log all user actions for compliance

### Session Security
- [ ] **Session Management UI** - View active sessions
- [ ] **Session Termination** - Ability to terminate active sessions

---

## Collaboration & Investigation

### Case Management
- [ ] **Case Management Workspaces** - Collaborative "digital war rooms" for investigations
- [ ] **Evidence Organization** - Organize evidence for specific investigations
- [ ] **Team Collaboration** - Multi-user collaboration on cases

### Crowdsourced Intelligence
- [ ] **Public Tip Submission** - Allow public users to submit tips and evidence
- [ ] **Triage Queue** - Queue system for analyzing submitted tips
- [ ] **Crowdsourced Verification** - Community-powered verification workflows

### Training
- [ ] **Training & Simulation Mode** - Sandbox environment for training new analysts
- [ ] **Demo Data** - Sample data that doesn't affect live database

---

## Deployment & Configuration

### Installation
- [ ] **Web-Based Installation Wizard** - Guided setup for database, admin user, settings
- [ ] **Command Line Installation** - CLI-based installation option
- [ ] **Shared Hosting Installation** - Installation without CLI access

### Docker
- [ ] **Docker Containerization** - Full Docker support
- [ ] **Demo Script (start-demo.sh)** - Single-command demo deployment
- [ ] **Docker Compose Configuration** - Multi-container orchestration

### System Configuration
- [ ] **Branding Configuration** - Customize platform branding
- [ ] **Map Defaults Configuration** - Configure default map settings
- [ ] **System Preferences** - General system configuration options

### Health & Monitoring
- [ ] **Health Check Endpoint (/health)** - System health monitoring
- [ ] **Database Connectivity Check** - Verify database connection
- [ ] **Cache Functionality Check** - Verify cache is working
- [ ] **Search Service Check** - Verify Meilisearch (if used)
- [ ] **External Service Dependency Check** - Verify external services

---

## Database & Seeding

### Database Structure
- [ ] **MySQL 8.0+ with Spatial Extensions** - Database setup
- [ ] **Proper Indexes** - Index foreign keys and frequently queried columns

### Data Seeding
- [ ] **Actor Seeders** - Seed realistic actor data
- [ ] **Conflict Seeders** - Seed conflict data
- [ ] **Equipment Seeders** - Seed equipment data (190+ items)
- [ ] **Event Type Seeders** - Seed 49+ event types

---

## API & Integration

### REST API
- [ ] **Full REST API** - Complete API for external integration
- [ ] **API Authentication (Sanctum)** - Secure API authentication
- [ ] **API Rate Limiting** - Prevent API abuse
- [ ] **Paginated Responses** - Proper pagination for list endpoints

### Data Pipeline
- [ ] **Automated Data Ingestion** - Automated data import capabilities
- [ ] **External Tool Integration** - Integration with third-party tools

---

## Offline Capability

- [ ] **Offline Mode** - Essential features accessible without internet
- [ ] **Local Data Caching** - Cache data for offline access
- [ ] **Sync on Reconnect** - Synchronize when connectivity is restored

---

## Legend

- `[ ]` - Not started
- `[x]` - Completed
- `[~]` - In progress

---

*Last updated: 2026-01-16*
