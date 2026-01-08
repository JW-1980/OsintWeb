# OsintWeb - The Future of Conflict Intelligence

## Turn Chaos into Clarity. Master the Battlefield of Information.

In an era of rapid geopolitical shifts and information overload, understanding the true state of global conflicts is not just an advantage—it's a necessity. From verified equipment losses to real-time territorial shifts, the difference between noise and intelligence is the tool you use to analyze it.

## 👁️ Attention: See What Others Miss

**OsintWeb** is the premier Open Source Intelligence platform designed for the modern analyst. Whether you are tracking the movement of naval carrier groups, verifying the destruction of armored vehicles, or mapping the fluctuating frontlines of asymmetric warfare, OsintWeb brings every data point into a single, cohesive operational picture.

Stop wrestling with spreadsheets and disjointed maps. Start seeing the conflict as it truly is.

## 🧠 Interest: Why OsintWeb?

Intelligence is only as good as its organization. OsintWeb transforms raw reports into verifiable, structured intelligence. It doesn't just show you *where* something happened; it tells you *who* did it, *what* was used, and *how* it impacts the broader conflict.

Imagine a system that:
*   Automatically connects an event to the specific military unit involved.
*   Visualizes the historical progression of a frontline with a simple slider.
*   Lets you attribute attacks to state actors or non-state militias with granular precision.
*   Empowers your community to crowdsource verification while maintaining a rigorous audit trail.

This is not just a map. It's a living history of the conflict.

## 💡 Desire: Extensive Feature Suite

Unlock a comprehensive arsenal of analysis tools designed for depth and precision.

### 🗺️ Interactive & Satellite Mapping
Dominate the visual landscape. Our advanced mapping engine allows you to draw complex control zones, color-coded by faction, to visualize territorial control instantly. Toggle between high-resolution satellite imagery and topographic maps to analyze terrain features. The dynamic legend adapts in real-time, ensuring your reports are always clear and professional. Export your operational picture as high-resolution (300 DPI) images for briefings and publications.

### 🚜 Military Equipment Database
Access a military encyclopedia at your fingertips. The platform comes pre-loaded with over **140 detailed equipment profiles**, ranging from *Gerald R. Ford-class* aircraft carriers to *T-90M* main battle tanks and *Switchblade* drones. Each entry tracks critical specifications like range, armament, and dimensions. More importantly, it tracks **inventory and attrition**—allowing you to monitor verified losses and remaining stock for every country in a conflict.

### 💥 Event Documentation & Forensics
Document the reality of war with precision. Utilize **49+ specialized event templates** covering everything from airstrikes and naval engagements to cyber attacks and humanitarian crises. Every event supports rich media attachments—images, videos, and documents—creating a verifiable chain of evidence. Link specific equipment losses to events to build a forensic record of the battlefield.

### 🕵️ Advanced Actor Attribution
Modern warfare is complex. OsintWeb creates a clear web of attribution, tracking **197 state actors** alongside hundreds of non-state groups, PMCs (like Wagner), militias, and terrorist organizations. Our smart system understands alliances and proxies, allowing you to attribute actions correctly—whether it's a state army or a shadow group. Real-time fuzzy search ensures you never miss a connection.

### ⏱️ Temporal Analysis & Time-Travel
History is a sequence of events. Our **Timeline Analysis** tools let you scrub through time, watching frontlines shift and battles unfold day-by-day. Use the **Chronological Builder** to construct custom investigation timelines, linking disparate events into a cohesive narrative to understand the cause and effect of military operations.

### 📝 Intelligence Reporting & Publishing
Turn analysis into influence. The built-in **CMS** allows you to publish deep-dive situation reports, daily briefings, and investigative articles. With support for premium content gates, SEO optimization, and rich media embedding, your analysis reaches the right audience with maximum impact.

### 🔒 Enterprise-Grade Security & RBAC
Trust is paramount. Protect your intelligence with **Role-Based Access Control (RBAC)**. Assign granular permissions (over 50 types) to analysts, moderators, and viewers. Every action is logged in a cryptographic **Audit Trail**, ensuring data integrity and accountability. The system includes full **GDPR compliance** tools, ensuring you meet privacy standards while managing sensitive data.

### 🏆 Achievement & Reputation System
Build a community of trusted analysts. The configurable **Achievement System** rewards high-quality contributions with medals, titles, and ranks. Gamify the verification process to encourage engagement and recognize your most valuable contributors.

## 🎬 Action: Deploy Your Intelligence Platform Today

Ready to start tracking? You can deploy a fully functional demonstration environment in minutes.

### 🚀 Quick Start (Local Demo)

See OsintWeb in action immediately with our single-command deployment. This will set up the entire stack and populate it with mock conflict data so you can explore the features right away.

*(Requires Docker)*

```bash
# Clone the repository
git clone https://github.com/your-org/osintweb.git
cd osintweb

# Run the demo script
./start-demo.sh
```

**What this does:**
1.  Builds the secure application container.
2.  Initializes the database and installs dependencies.
3.  **Seeds the system** with realistic actors, conflicts, and equipment data.
4.  Launches your personal intelligence platform at `http://localhost:8000`.

---

## 📦 Detailed Installation

For production environments or manual setup without Docker, follow these steps.

### Prerequisites

**Minimum Requirements:**
- PHP 8.2+ with extensions: mysql, mbstring, xml, curl, zip, bcmath, gd
- MySQL 8.0+ with spatial support
- Node.js 18+ and npm
- Composer 2.0+

### Installation Methods

#### Option 1: Web-Based Installation Wizard (Recommended)

The easiest way to install OsintWeb is through the interactive web-based wizard:

```bash
# Clone repository
git clone https://github.com/your-org/osintweb.git
cd osintweb

# Install dependencies
composer install
npm install && npm run build

# Copy environment file
cp .env.example .env

# Start development server
php artisan serve
```

Then visit `http://localhost:8000`. The installation wizard will guide you through database configuration, admin account creation, and system settings.

#### Option 2: Command Line Installation

```bash
# Clone and install
git clone https://github.com/your-org/osintweb.git
cd osintweb
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure database in .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# ...

# Create database & Run migrations
php artisan migrate --seed

# Build frontend
npm run build

# Start server
php artisan serve
```

---

*OsintWeb is built for the community. Join us in bringing transparency to global conflicts.*
