---
name: webdesign
description: Web design patterns for Laravel/Inertia applications with Tailwind CSS
tags: [webdesign, ui, ux, tailwind, vue, inertia, responsive, accessibility]
---

# Web Design for Laravel/Inertia Applications

This skill helps with implementing professional, accessible, and responsive web interfaces for Laravel applications using Inertia.js, Vue.js, and Tailwind CSS.

## When to Use

- Designing new pages or components
- Implementing responsive layouts
- Creating forms and data tables
- Building dashboards
- Improving accessibility
- Standardizing UI patterns
- Troubleshooting layout issues
- Optimizing for mobile devices

## Technology Stack

### Frontend Stack
- **Inertia.js**: Server-side routing with SPA experience
- **Vue.js 3**: Component-based UI framework
- **Tailwind CSS**: Utility-first CSS framework
- **Headless UI**: Unstyled accessible components
- **Heroicons**: SVG icon library

### Design Principles
- **Mobile-first**: Start with mobile, enhance for desktop
- **Accessibility**: WCAG 2.1 AA compliance
- **Consistency**: Reusable components and patterns
- **Performance**: Optimized assets, lazy loading
- **Dutch Language**: All UI text in Dutch

## Layout Design Fundamentals

Understanding layout fundamentals is essential for creating effective, user-friendly web interfaces. This section covers the core principles and patterns that guide professional web design.

### Visual Flow & Eye-Tracking Patterns

Users scan web pages in predictable patterns. Designing with these patterns in mind improves comprehension and guides users to key actions.

#### Z-Pattern (Landing Pages & Simple Layouts)

The Z-pattern works best for pages with minimal text and clear calls-to-action. Users' eyes move in a Z-shape across the page.

```
┌─────────────────────────────────────┐
│ LOGO          NAV MENU         CTA  │ ← Top horizontal scan
│                               ↙     │
│         ↙                           │
│    ↙                                │
│ ↙                                   │
│ HERO IMAGE     HEADLINE             │
│                                     │
│              DESCRIPTION         ↙  │
│                           ↙         │
│                    ↙                │
│             ↙                       │
│       ↙                             │
│  CTA BUTTON          SOCIAL  LINKS  │ ← Bottom horizontal scan
└─────────────────────────────────────┘
```

**Implementation Example**:
```vue
<template>
  <div class="min-h-screen bg-white">
    <!-- Top bar: Logo → Nav → CTA (left to right) -->
    <header class="flex items-center justify-between px-6 py-4 border-b">
      <img src="/logo.svg" alt="Logo" class="h-8" />
      <nav class="hidden md:flex gap-6">
        <a href="#features" class="text-gray-600 hover:text-gray-900">Features</a>
        <a href="#pricing" class="text-gray-600 hover:text-gray-900">Prijzen</a>
      </nav>
      <button class="btn-primary">
        Start Gratis
      </button>
    </header>

    <!-- Hero section: Diagonal flow from top-left to center -->
    <section class="px-6 py-20 text-center">
      <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl lg:text-6xl">
        Eenvoudig Boekhouden
      </h1>
      <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
        Professionele facturatie en boekhouding voor ondernemers
      </p>

      <!-- Bottom CTA: User's eyes end here -->
      <div class="mt-8 flex justify-center gap-4">
        <button class="btn-primary text-lg px-8 py-3">
          Probeer Nu
        </button>
        <button class="btn-secondary text-lg px-8 py-3">
          Meer Info
        </button>
      </div>
    </section>
  </div>
</template>
```

#### F-Pattern (Content-Heavy Pages & Lists)

The F-pattern is common on pages with lots of text, like blogs, documentation, or data tables. Users scan horizontally at the top, then vertically down the left side.

```
┌─────────────────────────────────────┐
│ ═══════════════════════════════     │ ← First horizontal scan (headline)
│ ║                                   │
│ ║ ════════════════════              │ ← Second horizontal scan (subheading)
│ ║                                   │
│ ║                                   │ ↓ Vertical scan down left side
│ ║ ════════                          │ ← Shorter horizontal scans
│ ║                                   │
│ ║                                   │ ↓
│ ║ ═════                             │
│ ║                                   │
│ ║                                   │ ↓
│ ║                                   │
└─────────────────────────────────────┘
```

**Implementation Example**:
```vue
<template>
  <article class="max-w-4xl mx-auto px-6 py-12">
    <!-- Top horizontal bar: Most important content -->
    <h1 class="text-3xl font-bold text-gray-900">
      Facturen Beheren in 5 Stappen
    </h1>

    <!-- Secondary horizontal bar -->
    <p class="mt-4 text-lg text-gray-600">
      Een complete handleiding voor efficiënt factuurbeheer
    </p>

    <!-- Left-aligned content with strong left edge -->
    <div class="mt-8 space-y-8">
      <!-- Strong left alignment guides vertical scan -->
      <section>
        <h2 class="text-xl font-semibold text-gray-900">
          1. Factuur Aanmaken
        </h2>
        <p class="mt-2 text-gray-600">
          Begin met het selecteren van een klant en voeg factuurregels toe...
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">
          2. Details Controleren
        </h2>
        <p class="mt-2 text-gray-600">
          Controleer alle gegevens voordat u de factuur verstuurt...
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">
          3. Verzenden naar Klant
        </h2>
        <p class="mt-2 text-gray-600">
          Verstuur de factuur direct via e-mail of download als PDF...
        </p>
      </section>
    </div>
  </article>
</template>
```

### Core Layout Principles

#### 1. Visual Hierarchy

Visual hierarchy guides users through content by making some elements more prominent than others.

**Techniques**:
- **Size**: Larger elements attract more attention
- **Weight**: Bold text stands out from regular text
- **Color**: High contrast draws the eye
- **Position**: Top and left items are seen first
- **Spacing**: Isolated elements get more attention

```vue
<template>
  <div class="space-y-6">
    <!-- Primary: Largest, boldest, darkest -->
    <h1 class="text-4xl font-bold text-gray-900">
      Dashboard
    </h1>

    <!-- Secondary: Medium size, medium weight -->
    <h2 class="text-2xl font-semibold text-gray-800">
      Recente Facturen
    </h2>

    <!-- Tertiary: Smaller, lighter -->
    <p class="text-base text-gray-600">
      Bekijk en beheer uw openstaande facturen
    </p>

    <!-- Least important: Smallest, lightest -->
    <p class="text-sm text-gray-500">
      Laatst bijgewerkt: 2 minuten geleden
    </p>
  </div>
</template>
```

#### 2. Balance

Balance creates visual stability and determines how "weighted" a layout feels.

**Symmetrical Balance**: Mirror image, formal and stable
```vue
<template>
  <!-- Centered, equal weight on both sides -->
  <div class="flex items-center justify-center py-12">
    <div class="text-center max-w-2xl">
      <h1 class="text-4xl font-bold">Welcome</h1>
      <p class="mt-4 text-lg">Centered content creates formal balance</p>
      <div class="mt-6 flex justify-center gap-4">
        <button class="btn-primary">Button 1</button>
        <button class="btn-primary">Button 2</button>
      </div>
    </div>
  </div>
</template>
```

**Asymmetrical Balance**: Different elements balanced by weight, more dynamic
```vue
<template>
  <!-- Large image on left balanced by text + button on right -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
    <!-- Large visual weight -->
    <div>
      <img src="/hero.jpg" alt="Hero" class="rounded-lg shadow-xl" />
    </div>

    <!-- Multiple smaller elements balance the image -->
    <div class="space-y-4">
      <h2 class="text-3xl font-bold">Heading</h2>
      <p class="text-lg text-gray-600">Description text...</p>
      <ul class="space-y-2">
        <li>Feature 1</li>
        <li>Feature 2</li>
        <li>Feature 3</li>
      </ul>
      <button class="btn-primary">Call to Action</button>
    </div>
  </div>
</template>
```

#### 3. Contrast

Contrast creates visual interest and guides attention to important elements.

```vue
<template>
  <div class="space-y-6">
    <!-- Size contrast -->
    <h1 class="text-5xl font-bold">Large Heading</h1>
    <p class="text-base">Regular paragraph text</p>

    <!-- Weight contrast -->
    <div>
      <span class="font-bold">Bold label:</span>
      <span class="font-normal">Normal value</span>
    </div>

    <!-- Color contrast -->
    <div class="flex gap-4">
      <button class="bg-blue-600 text-white px-6 py-3 rounded-lg">
        Primary Action
      </button>
      <button class="bg-white text-gray-700 border border-gray-300 px-6 py-3 rounded-lg">
        Secondary Action
      </button>
    </div>

    <!-- Background contrast -->
    <div class="bg-blue-50 p-6 rounded-lg">
      <p class="text-blue-900">
        Highlighted content with background contrast
      </p>
    </div>
  </div>
</template>
```

#### 4. White Space (Negative Space)

White space improves readability, creates relationships, and gives designs room to breathe.

```vue
<template>
  <!-- Bad: Cramped, hard to read -->
  <div class="bad-example">
    <h2 class="text-xl">Heading</h2>
    <p class="text-sm">Paragraph with no spacing makes it hard to read and overwhelming.</p>
    <button class="btn">Click</button>
  </div>

  <!-- Good: Generous spacing improves readability -->
  <div class="space-y-6">
    <h2 class="text-2xl font-semibold">
      Heading with Breathing Room
    </h2>

    <p class="text-base leading-relaxed text-gray-600">
      Adequate line height and spacing between elements makes content
      easier to scan and more pleasant to read.
    </p>

    <button class="btn-primary mt-4">
      Call to Action
    </button>
  </div>

  <!-- White space groups related elements -->
  <div class="space-y-8">
    <!-- Group 1: Tight spacing shows relationship -->
    <div class="space-y-2">
      <h3 class="font-semibold">Contact Info</h3>
      <p>info@example.com</p>
      <p>+31 20 123 4567</p>
    </div>

    <!-- Large gap separates unrelated groups -->

    <!-- Group 2: Another tight group -->
    <div class="space-y-2">
      <h3 class="font-semibold">Address</h3>
      <p>Straatnaam 123</p>
      <p>1234 AB Amsterdam</p>
    </div>
  </div>
</template>
```

#### 5. Alignment

Alignment creates visual connections and order. Every element should align with something else.

```vue
<template>
  <!-- Good: Everything aligns on a vertical line -->
  <div class="max-w-lg">
    <h2 class="text-2xl font-bold text-gray-900">
      Account Settings
    </h2>

    <div class="mt-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">
          Email Address
        </label>
        <input type="email" class="mt-1 w-full form-input" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">
          Password
        </label>
        <input type="password" class="mt-1 w-full form-input" />
      </div>

      <div class="flex justify-end gap-3">
        <button class="btn-secondary">Cancel</button>
        <button class="btn-primary">Save Changes</button>
      </div>
    </div>
  </div>

  <!-- Grid alignment example -->
  <div class="grid grid-cols-[120px_1fr] gap-4 items-start">
    <label class="text-sm font-medium text-gray-700 pt-2">
      Full Name
    </label>
    <input type="text" class="form-input" />

    <label class="text-sm font-medium text-gray-700 pt-2">
      Email
    </label>
    <input type="email" class="form-input" />

    <label class="text-sm font-medium text-gray-700 pt-2">
      Bio
    </label>
    <textarea class="form-input" rows="4"></textarea>
  </div>
</template>
```

#### 6. Proximity

Group related items together. Items close together are perceived as related.

```vue
<template>
  <!-- Good: Related items grouped with proximity -->
  <div class="space-y-8">
    <!-- Card 1: Tight internal spacing -->
    <div class="bg-white p-6 rounded-lg shadow">
      <div class="space-y-3">
        <h3 class="text-lg font-semibold">Factuur #INV-001</h3>
        <p class="text-gray-600">Klant: Bedrijf BV</p>
        <p class="text-2xl font-bold text-gray-900">€1.250,00</p>
      </div>
    </div>

    <!-- Large gap shows these are separate -->

    <!-- Card 2: Another independent group -->
    <div class="bg-white p-6 rounded-lg shadow">
      <div class="space-y-3">
        <h3 class="text-lg font-semibold">Factuur #INV-002</h3>
        <p class="text-gray-600">Klant: Andere BV</p>
        <p class="text-2xl font-bold text-gray-900">€890,00</p>
      </div>
    </div>
  </div>

  <!-- Form example with proximity grouping -->
  <form class="space-y-8">
    <!-- Personal info group -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold text-gray-900 pb-2 border-b">
        Persoonlijke Gegevens
      </h3>
      <div class="space-y-3">
        <input placeholder="Voornaam" class="form-input" />
        <input placeholder="Achternaam" class="form-input" />
        <input placeholder="Email" class="form-input" />
      </div>
    </div>

    <!-- Company info group - separated by space -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold text-gray-900 pb-2 border-b">
        Bedrijfsgegevens
      </h3>
      <div class="space-y-3">
        <input placeholder="Bedrijfsnaam" class="form-input" />
        <input placeholder="KVK-nummer" class="form-input" />
        <input placeholder="BTW-nummer" class="form-input" />
      </div>
    </div>
  </form>
</template>
```

### Grid Systems

A grid system provides structure and consistency across your design.

#### 12-Column Grid

The 12-column grid is standard because 12 is divisible by 2, 3, 4, and 6, offering maximum flexibility.

**Common Column Distributions**:
```
12 columns:  [────────────────────────────] (Full width)
6 + 6:       [────────────] [────────────] (50/50 split)
4 + 4 + 4:   [────────] [────────] [────────] (Thirds)
8 + 4:       [──────────────────] [────────] (66/33 split)
3 + 6 + 3:   [──────] [────────────] [──────] (Sidebar/Content/Sidebar)
3 + 3 + 3 + 3: [──────] [──────] [──────] [──────] (Quarters)
```

#### Tailwind Grid Implementation

```vue
<template>
  <!-- Basic responsive grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">Column 1</div>
    <div class="bg-white p-6 rounded-lg shadow">Column 2</div>
    <div class="bg-white p-6 rounded-lg shadow">Column 3</div>
    <div class="bg-white p-6 rounded-lg shadow">Column 4</div>
  </div>

  <!-- 12-column grid with custom spans -->
  <div class="grid grid-cols-12 gap-6">
    <!-- 8 columns (66%) -->
    <div class="col-span-12 lg:col-span-8 bg-white p-6 rounded-lg shadow">
      <h2 class="text-2xl font-bold">Main Content</h2>
      <p class="mt-4">This takes up 8 columns on large screens...</p>
    </div>

    <!-- 4 columns (33%) -->
    <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold">Sidebar</h3>
      <p class="mt-4">This takes up 4 columns...</p>
    </div>
  </div>

  <!-- Complex layout with different spans -->
  <div class="grid grid-cols-12 gap-6">
    <!-- Full width header -->
    <div class="col-span-12 bg-blue-600 text-white p-6 rounded-lg">
      <h1 class="text-3xl font-bold">Page Header</h1>
    </div>

    <!-- 3-column sidebar -->
    <aside class="col-span-12 md:col-span-3 bg-white p-6 rounded-lg shadow">
      <nav class="space-y-2">
        <a href="#" class="block p-2 rounded hover:bg-gray-100">Link 1</a>
        <a href="#" class="block p-2 rounded hover:bg-gray-100">Link 2</a>
      </nav>
    </aside>

    <!-- 9-column main content -->
    <main class="col-span-12 md:col-span-9 space-y-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold">Main Content Area</h2>
      </div>
    </main>
  </div>

  <!-- Dashboard grid with mixed column spans -->
  <div class="grid grid-cols-12 gap-6">
    <!-- 4 stat cards, each 3 columns (25% width) -->
    <div class="col-span-6 lg:col-span-3 bg-white p-6 rounded-lg shadow">
      <p class="text-sm text-gray-600">Total Revenue</p>
      <p class="text-3xl font-bold">€12,450</p>
    </div>
    <div class="col-span-6 lg:col-span-3 bg-white p-6 rounded-lg shadow">
      <p class="text-sm text-gray-600">Invoices</p>
      <p class="text-3xl font-bold">24</p>
    </div>
    <div class="col-span-6 lg:col-span-3 bg-white p-6 rounded-lg shadow">
      <p class="text-sm text-gray-600">Clients</p>
      <p class="text-3xl font-bold">12</p>
    </div>
    <div class="col-span-6 lg:col-span-3 bg-white p-6 rounded-lg shadow">
      <p class="text-sm text-gray-600">Overdue</p>
      <p class="text-3xl font-bold text-red-600">3</p>
    </div>

    <!-- Chart takes 8 columns -->
    <div class="col-span-12 lg:col-span-8 bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold">Revenue Chart</h3>
      <!-- Chart component -->
    </div>

    <!-- Recent activity takes 4 columns -->
    <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold">Recent Activity</h3>
      <!-- Activity list -->
    </div>
  </div>
</template>
```

#### Gap Utilities

Control spacing between grid items:

```vue
<template>
  <!-- Different gap sizes -->
  <div class="grid grid-cols-3 gap-4">     <!-- 16px gap -->
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
  </div>

  <div class="grid grid-cols-3 gap-6">     <!-- 24px gap -->
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
  </div>

  <div class="grid grid-cols-3 gap-8">     <!-- 32px gap -->
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
  </div>

  <!-- Different horizontal and vertical gaps -->
  <div class="grid grid-cols-2 gap-x-8 gap-y-4">
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
    <div class="bg-gray-200 p-4">Item</div>
  </div>

  <!-- Responsive gaps -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
    <!-- Gap increases on larger screens -->
  </div>
</template>
```

### Spacing System

Consistent spacing creates visual rhythm and makes designs feel cohesive. Use an 8px base unit for a harmonious scale.

#### Standard Spacing Scale (8px base)

```
xs:   4px   (0.5 × base)  - Minimal spacing, tight grouping
sm:   8px   (1 × base)    - Small spacing within components
md:   16px  (2 × base)    - Default spacing between elements
lg:   24px  (3 × base)    - Section spacing
xl:   32px  (4 × base)    - Large section spacing
2xl:  48px  (6 × base)    - Major section breaks
3xl:  64px  (8 × base)    - Page section dividers
```

#### Tailwind Spacing Implementation

```vue
<template>
  <!-- Component internal spacing (tight) -->
  <div class="bg-white p-4 rounded-lg shadow">      <!-- p-4 = 16px padding -->
    <h3 class="text-lg font-semibold">Card Title</h3>
    <p class="mt-2 text-gray-600">Description</p>   <!-- mt-2 = 8px margin-top -->
    <button class="mt-4 btn-primary">Action</button> <!-- mt-4 = 16px margin-top -->
  </div>

  <!-- Spacing between components -->
  <div class="space-y-6">                           <!-- 24px between children -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h2>Section 1</h2>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
      <h2>Section 2</h2>
    </div>
  </div>

  <!-- Page-level spacing -->
  <div class="px-6 py-12">                          <!-- 24px horizontal, 48px vertical -->
    <h1 class="text-4xl font-bold">Page Title</h1>

    <div class="mt-8 space-y-8">                    <!-- 32px top margin, 32px between sections -->
      <section>
        <h2 class="text-2xl font-semibold">Section 1</h2>
        <div class="mt-4 space-y-4">                <!-- 16px between paragraphs -->
          <p>Paragraph 1</p>
          <p>Paragraph 2</p>
        </div>
      </section>

      <section>
        <h2 class="text-2xl font-semibold">Section 2</h2>
        <div class="mt-4 space-y-4">
          <p>Paragraph 1</p>
          <p>Paragraph 2</p>
        </div>
      </section>
    </div>
  </div>

  <!-- Responsive spacing -->
  <div class="px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <!-- Padding increases on larger screens -->
  </div>
</template>
```

#### Spacing Best Practices

```vue
<template>
  <!-- Consistent vertical rhythm -->
  <article class="max-w-3xl mx-auto px-6 py-12">
    <h1 class="text-4xl font-bold">
      Article Title
    </h1>

    <p class="mt-4 text-lg text-gray-600">
      Lead paragraph with moderate spacing
    </p>

    <div class="mt-8 space-y-4">                    <!-- Body paragraphs -->
      <p class="leading-relaxed">
        First paragraph of the article...
      </p>
      <p class="leading-relaxed">
        Second paragraph...
      </p>
    </div>

    <h2 class="mt-12 text-2xl font-semibold">       <!-- Larger spacing before headings -->
      Subsection
    </h2>

    <div class="mt-4 space-y-4">
      <p class="leading-relaxed">Content...</p>
    </div>
  </article>

  <!-- Form spacing hierarchy -->
  <form class="space-y-8">                          <!-- Sections separated by 32px -->
    <div class="space-y-6">                         <!-- Field groups by 24px -->
      <h3 class="text-lg font-semibold">Personal Info</h3>

      <div class="space-y-4">                       <!-- Individual fields by 16px -->
        <div>
          <label class="block text-sm font-medium mb-1">
            First Name
          </label>
          <input type="text" class="form-input" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">
            Last Name
          </label>
          <input type="text" class="form-input" />
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <h3 class="text-lg font-semibold">Company Info</h3>
      <!-- More fields... -->
    </div>
  </form>
</template>
```

### Layout Workflow

Follow this systematic approach when designing a new page or component.

#### Step 1: Sketch/Wireframe

Start with low-fidelity wireframes to focus on structure, not details.

```
┌─────────────────────────────────────┐
│  HEADER                             │
├─────────────────────────────────────┤
│                                     │
│  [Hero Image]    │  Headline        │
│                  │  Description     │
│                  │  [CTA Button]    │
│                                     │
├─────────────────────────────────────┤
│  [Feature 1] [Feature 2] [Feature 3]│
├─────────────────────────────────────┤
│  FOOTER                             │
└─────────────────────────────────────┘

Tools: Paper sketch, Figma, Excalidraw, or text diagrams
```

#### Step 2: Apply Grid

Map your wireframe to a grid system for consistency.

```vue
<template>
  <!-- Start with grid structure -->
  <div class="min-h-screen bg-gray-50">
    <!-- Header: Full width -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <!-- Header content -->
      </div>
    </header>

    <!-- Hero: 12-column grid -->
    <section class="max-w-7xl mx-auto px-6 py-12">
      <div class="grid grid-cols-12 gap-8 items-center">
        <div class="col-span-6">
          <!-- Image -->
        </div>
        <div class="col-span-6">
          <!-- Content -->
        </div>
      </div>
    </section>

    <!-- Features: 3-column grid -->
    <section class="max-w-7xl mx-auto px-6 py-12">
      <div class="grid grid-cols-3 gap-8">
        <!-- 3 feature cards -->
      </div>
    </section>

    <!-- Footer: Full width -->
    <footer class="bg-gray-900 text-white">
      <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Footer content -->
      </div>
    </footer>
  </div>
</template>
```

#### Step 3: Establish Hierarchy

Define visual importance with typography, size, and weight.

```vue
<template>
  <div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Primary: Page title -->
    <h1 class="text-4xl font-bold text-gray-900">
      Dashboard Overview
    </h1>

    <!-- Secondary: Section headers -->
    <div class="mt-12 space-y-8">
      <section>
        <h2 class="text-2xl font-semibold text-gray-800">
          Recent Activity
        </h2>

        <!-- Tertiary: Sub-sections -->
        <div class="mt-6">
          <h3 class="text-lg font-medium text-gray-700">
            Today
          </h3>

          <!-- Body: Regular content -->
          <div class="mt-4 space-y-2">
            <p class="text-base text-gray-600">
              Invoice #001 created
            </p>
            <p class="text-base text-gray-600">
              Payment received
            </p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
```

#### Step 4: Place Content

Add actual content, imagery, and interactive elements.

```vue
<template>
  <div class="grid grid-cols-12 gap-8">
    <div class="col-span-6">
      <!-- Add real image -->
      <img
        src="/images/dashboard-preview.png"
        alt="Dashboard Preview"
        class="rounded-lg shadow-xl"
      />
    </div>

    <div class="col-span-6 space-y-6">
      <h1 class="text-4xl font-bold text-gray-900">
        Professioneel Boekhouden
      </h1>

      <p class="text-lg text-gray-600">
        Beheer facturen, volg betalingen, en genereer rapporten
        met ons gebruiksvriendelijke platform.
      </p>

      <!-- Add interactive elements -->
      <div class="flex gap-4">
        <button class="btn-primary">
          Probeer Gratis
        </button>
        <button class="btn-secondary">
          Bekijk Demo
        </button>
      </div>

      <!-- Add feature list -->
      <ul class="space-y-2">
        <li class="flex items-center gap-2">
          <CheckIcon class="h-5 w-5 text-green-500" />
          <span>Automatische factuurherkenning</span>
        </li>
        <li class="flex items-center gap-2">
          <CheckIcon class="h-5 w-5 text-green-500" />
          <span>Real-time rapportage</span>
        </li>
      </ul>
    </div>
  </div>
</template>
```

#### Step 5: Refine Spacing

Adjust spacing for visual rhythm and breathing room.

```vue
<template>
  <!-- Before: Cramped -->
  <div class="space-y-2">
    <h1 class="text-4xl font-bold">Title</h1>
    <p class="text-lg">Description</p>
    <button class="btn-primary">Action</button>
  </div>

  <!-- After: Proper spacing -->
  <div class="space-y-6">                           <!-- Increased from 2 to 6 -->
    <h1 class="text-4xl font-bold">Title</h1>

    <p class="text-lg text-gray-600 leading-relaxed"> <!-- Added leading-relaxed -->
      Description with better line height and color
    </p>

    <div class="pt-2">                               <!-- Extra spacing before CTA -->
      <button class="btn-primary">Action</button>
    </div>
  </div>
</template>
```

#### Step 6: Test Responsiveness

Verify the layout works on all screen sizes.

```vue
<template>
  <!-- Mobile-first responsive layout -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <!-- Stack on mobile, side-by-side on desktop -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-12">
      <div>
        <img
          src="/image.jpg"
          alt="Hero"
          class="w-full rounded-lg"
        />
      </div>

      <div class="space-y-4 lg:space-y-6">
        <!-- Smaller text on mobile -->
        <h1 class="text-3xl lg:text-5xl font-bold">
          Responsive Title
        </h1>

        <p class="text-base lg:text-lg text-gray-600">
          Description text
        </p>

        <!-- Stack buttons on mobile -->
        <div class="flex flex-col sm:flex-row gap-3">
          <button class="btn-primary w-full sm:w-auto">
            Primary
          </button>
          <button class="btn-secondary w-full sm:w-auto">
            Secondary
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Test breakpoints:
       - Mobile: < 640px (sm)
       - Tablet: 640px - 1024px (sm to lg)
       - Desktop: > 1024px (lg+)
  -->
</template>
```

### Typography for Hierarchy

Typography is the primary tool for establishing visual hierarchy and content structure.

#### Line Length (Measure)

Optimal reading comfort is achieved with 45-75 characters per line.

```vue
<template>
  <!-- Too wide: Hard to track from line to line -->
  <p class="max-w-none text-base">
    This line is too long and extends across the entire screen width making it difficult for readers to track from the end of one line to the beginning of the next which reduces reading comprehension and causes eye strain.
  </p>

  <!-- Optimal: 65 characters per line (approximately) -->
  <p class="max-w-2xl text-base">
    This line length is optimal for reading. The text is contained
    within a comfortable width that makes it easy to scan and read
    without losing your place.
  </p>

  <!-- Responsive line length -->
  <article class="max-w-none sm:max-w-3xl lg:max-w-4xl mx-auto px-6">
    <p class="text-base leading-relaxed">
      Content automatically adjusts to maintain readable line length
      across different screen sizes.
    </p>
  </article>
</template>
```

#### Type Scale

A consistent type scale creates clear hierarchy.

```vue
<template>
  <div class="space-y-8">
    <!-- Display: Largest, for hero sections -->
    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold leading-tight">
      Display Heading
    </h1>

    <!-- H1: Page titles -->
    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight">
      Page Title
    </h1>

    <!-- H2: Major sections -->
    <h2 class="text-2xl sm:text-3xl font-semibold leading-snug">
      Section Heading
    </h2>

    <!-- H3: Subsections -->
    <h3 class="text-xl sm:text-2xl font-semibold leading-snug">
      Subsection Heading
    </h3>

    <!-- H4: Component titles -->
    <h4 class="text-lg font-semibold leading-normal">
      Component Title
    </h4>

    <!-- Body Large: Introductory text -->
    <p class="text-lg leading-relaxed text-gray-600">
      Large body text for introductions and important descriptions.
    </p>

    <!-- Body: Regular content -->
    <p class="text-base leading-relaxed text-gray-600">
      Regular body text for most content. This is the default reading size.
    </p>

    <!-- Body Small: Secondary information -->
    <p class="text-sm leading-normal text-gray-500">
      Smaller text for less important information, labels, and metadata.
    </p>

    <!-- Caption: Minimal emphasis -->
    <p class="text-xs leading-normal text-gray-400">
      Caption text for timestamps, footnotes, and minimal information.
    </p>
  </div>
</template>
```

#### Practical Typography Component

```vue
<script setup>
defineProps({
  variant: {
    type: String,
    default: 'body',
    validator: (value) => [
      'display', 'h1', 'h2', 'h3', 'h4', 'body-large', 'body', 'body-small', 'caption'
    ].includes(value)
  }
})

const variantClasses = {
  'display': 'text-5xl sm:text-6xl lg:text-7xl font-bold leading-tight',
  'h1': 'text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight',
  'h2': 'text-2xl sm:text-3xl font-semibold leading-snug',
  'h3': 'text-xl sm:text-2xl font-semibold leading-snug',
  'h4': 'text-lg font-semibold leading-normal',
  'body-large': 'text-lg leading-relaxed',
  'body': 'text-base leading-relaxed',
  'body-small': 'text-sm leading-normal',
  'caption': 'text-xs leading-normal text-gray-500',
}
</script>

<template>
  <component
    :is="variant.startsWith('h') ? variant : 'p'"
    :class="variantClasses[variant]"
  >
    <slot />
  </component>
</template>

<!-- Usage -->
<template>
  <div class="space-y-6">
    <Typography variant="h1">
      Page Title
    </Typography>

    <Typography variant="body-large">
      Lead paragraph with larger text for emphasis.
    </Typography>

    <Typography variant="body">
      Regular body content goes here.
    </Typography>

    <Typography variant="caption">
      Last updated: 2 hours ago
    </Typography>
  </div>
</template>
```

## Layout Patterns

### 1. Authenticated Layout

**Purpose**: Standard layout for authenticated users with sidebar navigation

**Structure**:
```vue
<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg">
      <div class="flex h-16 items-center justify-center border-b">
        <img src="/logo.svg" alt="Logo" class="h-8" />
      </div>

      <nav class="mt-6 px-4">
        <NavLink href="/dashboard" :active="$page.url === '/dashboard'">
          <HomeIcon class="mr-3 h-5 w-5" />
          Dashboard
        </NavLink>
        <!-- More navigation items -->
      </nav>
    </aside>

    <!-- Main content -->
    <div class="ml-64 flex flex-col">
      <!-- Top bar -->
      <header class="flex h-16 items-center justify-between border-b bg-white px-6">
        <h1 class="text-xl font-semibold text-gray-900">{{ title }}</h1>
        <UserMenu :user="$page.props.auth.user" />
      </header>

      <!-- Page content -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
```

### 2. Responsive Sidebar

**Mobile**: Slide-over menu with backdrop
**Desktop**: Fixed sidebar

```vue
<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'

const sidebarOpen = ref(false)
</script>

<template>
  <!-- Mobile sidebar -->
  <TransitionRoot as="template" :show="sidebarOpen">
    <Dialog as="div" class="relative z-50 lg:hidden" @close="sidebarOpen = false">
      <TransitionChild
        as="template"
        enter="transition-opacity ease-linear duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="transition-opacity ease-linear duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/80" />
      </TransitionChild>

      <div class="fixed inset-0 flex">
        <TransitionChild
          as="template"
          enter="transition ease-in-out duration-300 transform"
          enter-from="-translate-x-full"
          enter-to="translate-x-0"
          leave="transition ease-in-out duration-300 transform"
          leave-from="translate-x-0"
          leave-to="-translate-x-full"
        >
          <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
            <!-- Sidebar content -->
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Desktop sidebar -->
  <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
    <!-- Sidebar content -->
  </div>
</template>
```

### 3. Dashboard Grid Layout

**Pattern**: Responsive grid with cards

```vue
<template>
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Stat cards -->
    <StatCard
      title="Totale omzet"
      :value="formatCurrency(stats.revenue)"
      :change="stats.revenueChange"
      trend="up"
      icon="CurrencyEuroIcon"
    />
    <StatCard
      title="Openstaande facturen"
      :value="stats.unpaidInvoices"
      :change="stats.unpaidChange"
      trend="down"
      icon="DocumentTextIcon"
    />
  </div>

  <div class="mt-6 grid gap-6 lg:grid-cols-2">
    <!-- Charts -->
    <Card>
      <CardHeader>
        <h3 class="text-lg font-medium">Omzet per maand</h3>
      </CardHeader>
      <CardContent>
        <RevenueChart :data="chartData" />
      </CardContent>
    </Card>

    <!-- Recent activity -->
    <Card>
      <CardHeader>
        <h3 class="text-lg font-medium">Recente activiteit</h3>
      </CardHeader>
      <CardContent>
        <ActivityList :items="recentActivity" />
      </CardContent>
    </Card>
  </div>
</template>
```

## Component Patterns

### 1. Form Components

**Standard Form Layout**:
```vue
<template>
  <form @submit.prevent="submit" class="space-y-6">
    <!-- Form fields -->
    <FormField label="Bedrijfsnaam" required error="errors.name">
      <TextInput
        v-model="form.name"
        placeholder="Bijv. Boekhouder BV"
        :error="errors.name"
        required
      />
    </FormField>

    <FormField label="KVK-nummer" required error="errors.kvk">
      <TextInput
        v-model="form.kvk"
        placeholder="12345678"
        maxlength="8"
        :error="errors.kvk"
        required
      />
    </FormField>

    <FormField label="BTW-nummer" error="errors.vat_number">
      <TextInput
        v-model="form.vat_number"
        placeholder="NL123456789B01"
        :error="errors.vat_number"
      />
    </FormField>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3">
      <SecondaryButton @click="cancel">
        Annuleren
      </SecondaryButton>
      <PrimaryButton type="submit" :loading="form.processing">
        Opslaan
      </PrimaryButton>
    </div>
  </form>
</template>
```

**Form Field Component**:
```vue
<script setup>
defineProps({
  label: String,
  required: Boolean,
  error: String,
  help: String,
})
</script>

<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="mt-1">
      <slot />
    </div>

    <p v-if="help && !error" class="mt-1 text-sm text-gray-500">
      {{ help }}
    </p>

    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>
```

### 2. Data Tables

**Responsive Table Pattern**:
```vue
<template>
  <div class="overflow-hidden bg-white shadow sm:rounded-lg">
    <!-- Table header with actions -->
    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
      <div>
        <h3 class="text-lg font-medium text-gray-900">Facturen</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ invoices.total }} facturen gevonden
        </p>
      </div>

      <div class="flex gap-3">
        <SearchInput v-model="search" placeholder="Zoeken..." />
        <PrimaryButton @click="createInvoice">
          <PlusIcon class="mr-2 h-4 w-4" />
          Nieuwe factuur
        </PrimaryButton>
      </div>
    </div>

    <!-- Desktop table -->
    <div class="hidden sm:block">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              Factuurnummer
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              Klant
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              Bedrag
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              Status
            </th>
            <th class="relative px-6 py-3">
              <span class="sr-only">Acties</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
              {{ invoice.number }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
              {{ invoice.client.name }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
              {{ formatCurrency(invoice.total) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <StatusBadge :status="invoice.status" />
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
              <TableActions :invoice="invoice" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile card list -->
    <div class="sm:hidden">
      <InvoiceCard
        v-for="invoice in invoices.data"
        :key="invoice.id"
        :invoice="invoice"
      />
    </div>

    <!-- Pagination -->
    <div class="border-t border-gray-200 px-6 py-4">
      <Pagination :links="invoices.links" />
    </div>
  </div>
</template>
```

### 3. Modal Dialogs

**Standard Modal Pattern**:
```vue
<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'

const props = defineProps({
  show: Boolean,
  title: String,
  maxWidth: { type: String, default: '2xl' },
})

const emit = defineEmits(['close'])

const maxWidthClass = {
  sm: 'sm:max-w-sm',
  md: 'sm:max-w-md',
  lg: 'sm:max-w-lg',
  xl: 'sm:max-w-xl',
  '2xl': 'sm:max-w-2xl',
}
</script>

<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              :class="maxWidthClass[maxWidth]"
              class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:p-6"
            >
              <div>
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                  {{ title }}
                </DialogTitle>

                <div class="mt-4">
                  <slot />
                </div>
              </div>

              <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse sm:gap-3">
                <slot name="footer" />
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
```

### 4. Status Badges

**Visual Status Indicators**:
```vue
<script setup>
const props = defineProps({
  status: String,
})

const statusConfig = {
  draft: { label: 'Concept', color: 'gray' },
  sent: { label: 'Verzonden', color: 'blue' },
  paid: { label: 'Betaald', color: 'green' },
  overdue: { label: 'Verlopen', color: 'red' },
  cancelled: { label: 'Geannuleerd', color: 'gray' },
}

const config = computed(() => statusConfig[props.status] || statusConfig.draft)

const colorClasses = {
  gray: 'bg-gray-100 text-gray-800',
  blue: 'bg-blue-100 text-blue-800',
  green: 'bg-green-100 text-green-800',
  red: 'bg-red-100 text-red-800',
  yellow: 'bg-yellow-100 text-yellow-800',
}
</script>

<template>
  <span
    :class="colorClasses[config.color]"
    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold leading-5"
  >
    {{ config.label }}
  </span>
</template>
```

## Tailwind CSS Patterns

### 1. Custom Component Classes

**Define reusable component classes**:
```css
/* resources/css/components.css */
@layer components {
  .btn {
    @apply inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
  }

  .btn-primary {
    @apply btn bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
  }

  .btn-secondary {
    @apply btn border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-blue-500;
  }

  .btn-danger {
    @apply btn bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
  }

  .card {
    @apply overflow-hidden rounded-lg bg-white shadow;
  }

  .card-header {
    @apply border-b border-gray-200 px-6 py-4;
  }

  .card-content {
    @apply px-6 py-4;
  }

  .form-input {
    @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
  }

  .form-input-error {
    @apply border-red-300 focus:border-red-500 focus:ring-red-500;
  }
}
```

### 2. Responsive Design Patterns

**Breakpoints** (Tailwind default):
- `sm`: 640px (small tablets)
- `md`: 768px (tablets)
- `lg`: 1024px (laptops)
- `xl`: 1280px (desktops)
- `2xl`: 1536px (large desktops)

**Mobile-first example**:
```vue
<div class="
  px-4           <!-- Mobile: 16px padding -->
  sm:px-6        <!-- Tablet: 24px padding -->
  lg:px-8        <!-- Desktop: 32px padding -->
  grid
  grid-cols-1    <!-- Mobile: 1 column -->
  sm:grid-cols-2 <!-- Tablet: 2 columns -->
  lg:grid-cols-4 <!-- Desktop: 4 columns -->
  gap-4          <!-- 16px gap -->
  lg:gap-6       <!-- Desktop: 24px gap -->
">
  <!-- Content -->
</div>
```

### 3. Dark Mode Support

**Add dark mode variants**:
```vue
<div class="
  bg-white        dark:bg-gray-900
  text-gray-900   dark:text-gray-100
  border-gray-200 dark:border-gray-700
">
  <h1 class="text-gray-900 dark:text-white">
    Title
  </h1>
  <p class="text-gray-600 dark:text-gray-400">
    Description
  </p>
</div>
```

**Enable dark mode** in `tailwind.config.js`:
```js
module.exports = {
  darkMode: 'class', // or 'media'
  // ... rest of config
}
```

## Accessibility Best Practices

### 1. Semantic HTML

```vue
<!-- Good: Semantic elements -->
<header>
  <nav aria-label="Hoofdnavigatie">
    <ul>
      <li><a href="/dashboard">Dashboard</a></li>
    </ul>
  </nav>
</header>

<main>
  <article>
    <h1>Factuurnummer INV-2025-001</h1>
    <section>
      <h2>Factuurdetails</h2>
      <!-- Content -->
    </section>
  </article>
</main>

<!-- Bad: Generic divs -->
<div class="header">
  <div class="nav">
    <div><a href="/dashboard">Dashboard</a></div>
  </div>
</div>
```

### 2. Keyboard Navigation

```vue
<template>
  <button
    type="button"
    class="btn-primary"
    @click="handleClick"
    @keydown.enter="handleClick"
    @keydown.space.prevent="handleClick"
  >
    Opslaan
  </button>

  <!-- Custom dropdown -->
  <div
    role="button"
    tabindex="0"
    @click="toggleDropdown"
    @keydown.enter="toggleDropdown"
    @keydown.space.prevent="toggleDropdown"
    @keydown.escape="closeDropdown"
  >
    Menu
  </div>
</template>
```

### 3. ARIA Labels

```vue
<template>
  <!-- Icon-only buttons -->
  <button
    type="button"
    aria-label="Verwijder factuur"
    @click="deleteInvoice"
  >
    <TrashIcon class="h-5 w-5" aria-hidden="true" />
  </button>

  <!-- Search input -->
  <label for="search" class="sr-only">Zoeken</label>
  <input
    id="search"
    type="search"
    placeholder="Zoeken..."
    aria-label="Zoeken in facturen"
  />

  <!-- Loading state -->
  <button
    type="submit"
    :disabled="loading"
    :aria-busy="loading"
  >
    <span v-if="loading" aria-live="polite">Laden...</span>
    <span v-else>Opslaan</span>
  </button>

  <!-- Error alerts -->
  <div
    v-if="error"
    role="alert"
    aria-live="assertive"
    class="rounded-md bg-red-50 p-4"
  >
    <p class="text-sm text-red-800">{{ error }}</p>
  </div>
</template>
```

### 4. Focus Management

```vue
<script setup>
import { ref, onMounted } from 'vue'

const firstInputRef = ref(null)

onMounted(() => {
  // Auto-focus first input on modal open
  firstInputRef.value?.focus()
})

const handleSubmit = () => {
  // Return focus to trigger element after modal closes
  document.getElementById('open-modal-button')?.focus()
}
</script>

<template>
  <Modal @close="handleClose">
    <form @submit.prevent="handleSubmit">
      <input
        ref="firstInputRef"
        type="text"
        placeholder="Naam"
      />
      <!-- More fields -->
    </form>
  </Modal>
</template>
```

## Dutch UI Copy Guidelines

### 1. Button Labels

```
✅ Good:
- Opslaan
- Annuleren
- Verwijderen
- Toevoegen
- Bewerken
- Verzenden
- Downloaden

❌ Bad (English):
- Save
- Cancel
- Delete
```

### 2. Form Validation Messages

```vue
const validationMessages = {
  required: 'Dit veld is verplicht',
  email: 'Voer een geldig e-mailadres in',
  min: 'Dit veld moet minimaal {min} tekens bevatten',
  max: 'Dit veld mag maximaal {max} tekens bevatten',
  numeric: 'Dit veld moet een getal zijn',
  kvk: 'Voer een geldig KVK-nummer in (8 cijfers)',
  vat: 'Voer een geldig BTW-nummer in',
  iban: 'Voer een geldig IBAN in',
}
```

### 3. Status Messages

```vue
const statusMessages = {
  success: {
    created: '{item} succesvol aangemaakt',
    updated: '{item} succesvol bijgewerkt',
    deleted: '{item} succesvol verwijderd',
    sent: '{item} succesvol verzonden',
  },
  error: {
    generic: 'Er is een fout opgetreden. Probeer het opnieuw.',
    notFound: '{item} niet gevonden',
    unauthorized: 'U heeft geen toestemming voor deze actie',
    validation: 'Controleer de ingevoerde gegevens',
  },
}
```

## Performance Optimization

### 1. Lazy Loading Components

```vue
<script setup>
import { defineAsyncComponent } from 'vue'

// Lazy load heavy components
const InvoiceChart = defineAsyncComponent(() =>
  import('./Components/InvoiceChart.vue')
)

const ReportGenerator = defineAsyncComponent(() =>
  import('./Components/ReportGenerator.vue')
)
</script>

<template>
  <Suspense>
    <template #default>
      <InvoiceChart :data="chartData" />
    </template>
    <template #fallback>
      <LoadingSpinner />
    </template>
  </Suspense>
</template>
```

### 2. Image Optimization

```vue
<template>
  <!-- Use responsive images -->
  <img
    src="/images/logo-small.png"
    srcset="
      /images/logo-small.png 1x,
      /images/logo-medium.png 2x,
      /images/logo-large.png 3x
    "
    alt="Company Logo"
    loading="lazy"
    class="h-8 w-auto"
  />

  <!-- Or use picture element -->
  <picture>
    <source
      media="(min-width: 1024px)"
      srcset="/images/banner-desktop.webp"
      type="image/webp"
    />
    <source
      media="(min-width: 640px)"
      srcset="/images/banner-tablet.webp"
      type="image/webp"
    />
    <img
      src="/images/banner-mobile.jpg"
      alt="Banner"
      loading="lazy"
    />
  </picture>
</template>
```

### 3. Debounce Search Input

```vue
<script setup>
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { router } from '@inertiajs/vue3'

const search = ref('')

const performSearch = useDebounceFn((value) => {
  router.get('/invoices', { search: value }, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

watch(search, (newValue) => {
  performSearch(newValue)
})
</script>

<template>
  <input
    v-model="search"
    type="search"
    placeholder="Zoeken..."
    class="form-input"
  />
</template>
```

## Common UI Patterns

### 1. Empty States

```vue
<template>
  <div v-if="invoices.length === 0" class="text-center py-12">
    <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
    <h3 class="mt-2 text-sm font-medium text-gray-900">
      Geen facturen gevonden
    </h3>
    <p class="mt-1 text-sm text-gray-500">
      Maak uw eerste factuur aan om te beginnen.
    </p>
    <div class="mt-6">
      <PrimaryButton @click="createInvoice">
        <PlusIcon class="mr-2 h-4 w-4" />
        Nieuwe factuur
      </PrimaryButton>
    </div>
  </div>
</template>
```

### 2. Loading States

```vue
<template>
  <!-- Skeleton loader -->
  <div v-if="loading" class="animate-pulse space-y-4">
    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
  </div>

  <!-- Content -->
  <div v-else>
    <!-- Actual content -->
  </div>

  <!-- Spinner -->
  <div v-if="processing" class="flex items-center justify-center py-8">
    <svg
      class="animate-spin h-8 w-8 text-blue-600"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>
  </div>
</template>
```

### 3. Confirmation Dialogs

```vue
<script setup>
import { ref } from 'vue'

const showDeleteConfirm = ref(false)
const itemToDelete = ref(null)

const confirmDelete = (item) => {
  itemToDelete.value = item
  showDeleteConfirm.value = true
}

const handleDelete = async () => {
  await deleteInvoice(itemToDelete.value.id)
  showDeleteConfirm.value = false
  itemToDelete.value = null
}
</script>

<template>
  <ConfirmDialog
    :show="showDeleteConfirm"
    title="Factuur verwijderen"
    message="Weet u zeker dat u deze factuur wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt."
    confirm-text="Verwijderen"
    confirm-color="danger"
    @confirm="handleDelete"
    @cancel="showDeleteConfirm = false"
  />
</template>
```

## Troubleshooting

### Issue 1: Tailwind Classes Not Applied

**Problem**: Tailwind classes not working in production

**Solution**:
```js
// tailwind.config.js
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './app/View/Components/**/*.php',
  ],
  // ... rest of config
}
```

### Issue 2: Inertia Link Not Preserving State

**Problem**: Page reloads on navigation

**Solution**:
```vue
<Link
  :href="route('invoices.show', invoice.id)"
  preserve-scroll
  preserve-state
>
  View Invoice
</Link>
```

### Issue 3: Modal Z-Index Issues

**Problem**: Modal appears behind other elements

**Solution**:
```vue
<!-- Ensure modal has high z-index -->
<Dialog as="div" class="relative z-50">
  <!-- Content -->
</Dialog>

<!-- Or adjust in tailwind.config.js -->
module.exports = {
  theme: {
    extend: {
      zIndex: {
        '60': '60',
        '70': '70',
        '80': '80',
        '90': '90',
        '100': '100',
      }
    }
  }
}
```

## Resources

- **Tailwind CSS**: https://tailwindcss.com/docs
- **Vue.js 3**: https://vuejs.org/
- **Inertia.js**: https://inertiajs.com/
- **Headless UI**: https://headlessui.com/
- **Heroicons**: https://heroicons.com/
- **WCAG Guidelines**: https://www.w3.org/WAI/WCAG21/quickref/
- **VueUse**: https://vueuse.org/ (composable utilities)

---

**Remember**: Always design mobile-first, ensure accessibility, and maintain consistency across the application!
