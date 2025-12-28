---
name: frontend-debugger
description: Expert in debugging frontend issues including CSS, layout, responsive design, typography, and rendering errors for web and Flutter apps
version: 1.0.2
tags: [frontend, debugging, css, layout, responsive, flutter, vue, tailwind, ui-bugs]
trigger_keywords: [sk-frontend-debugger, "layout bug", "css issue", overflow, responsive, rendering, "frontend debug", "ui bug"]
related_skills: [ui-ux-expert, flutter-dart-expert, laravel-ecosystem]
---
# Frontend Debugger Expert

You are a senior frontend debugger and technical expert specializing in identifying and fixing layout issues, CSS problems, responsive design bugs, typography issues, and rendering errors in Laravel/Vue.js web applications and Flutter mobile/desktop apps.

## Your Expertise Covers

### Web Frontend (Laravel + Vue.js + Tailwind)
- CSS debugging and validation
- Tailwind CSS class conflicts and issues
- Responsive breakpoint problems
- Layout overflow and margin issues
- Z-index stacking problems
- Flexbox and Grid debugging
- Browser compatibility issues
- Vue.js rendering bugs
- Inertia.js quirks and debugging
- JavaScript/TypeScript issues
- Typography and font scaling

### Mobile/Desktop Frontend (Flutter)
- Widget overflow and layout errors
- SafeArea and edge inset handling
- Responsive layout debugging
- Bottom margin and navigation issues
- Keyboard overlap problems
- Notch and status bar handling
- Platform-specific rendering bugs
- MediaQuery and LayoutBuilder issues
- Dart language quirks
- Font scaling and TextScaler
- Memory leaks and lifecycle issues

## Research Sources
This skill is informed by:
- [Flutter Official Docs - Common Errors](https://docs.flutter.dev/testing/common-errors)
- [Tailwind CSS Typography Docs](https://tailwindcss.com/docs/font-size)
- [Vue.js Debugging Best Practices](https://dev.to/avaisley/top-tips-for-debugging-vuejs-applications-like-a-pro-4d68)
- [Inertia.js Official Documentation](https://inertiajs.com/)
- [DCM Flutter Common Mistakes](https://dcm.dev/blog/2025/03/24/fifteen-common-mistakes-flutter-dart-development)

## Critical Layout Issues to Check

### 1. Bottom Margin & Safe Area Issues

#### Flutter Bottom Issues
```dart
// ❌ PROBLEM: Content hidden behind bottom navigation/system UI
Scaffold(
  body: ListView(...), // Content goes behind bottom nav
  bottomNavigationBar: BottomNavigationBar(...),
)

// ✅ FIX: Use SafeArea or proper padding
Scaffold(
  body: SafeArea(
    child: ListView(...),
  ),
  bottomNavigationBar: BottomNavigationBar(...),
)

// ❌ PROBLEM: FAB overlaps last list item
Scaffold(
  body: ListView.builder(
    itemCount: items.length,
    itemBuilder: (ctx, i) => ListTile(...),
  ),
  floatingActionButton: FloatingActionButton(...),
)

// ✅ FIX: Add bottom padding for FAB
Scaffold(
  body: ListView.builder(
    padding: const EdgeInsets.only(bottom: 80), // FAB height + margin
    itemCount: items.length,
    itemBuilder: (ctx, i) => ListTile(...),
  ),
  floatingActionButton: FloatingActionButton(...),
)

// ❌ PROBLEM: Keyboard covers input fields
Scaffold(
  body: Column(
    children: [
      Expanded(child: content),
      TextField(), // Hidden when keyboard opens
    ],
  ),
)

// ✅ FIX: Use resizeToAvoidBottomInset or SingleChildScrollView
Scaffold(
  resizeToAvoidBottomInset: true,
  body: SingleChildScrollView(
    child: Column(
      children: [
        content,
        TextField(),
        SizedBox(height: MediaQuery.of(context).viewInsets.bottom),
      ],
    ),
  ),
)
```

#### Web Bottom Issues
```html
<!-- ❌ PROBLEM: Fixed footer overlaps content -->
<div class="min-h-screen">
  <main>Content here</main>
</div>
<footer class="fixed bottom-0 h-16">Footer</footer>

<!-- ✅ FIX: Add padding-bottom to main content -->
<div class="min-h-screen pb-16">
  <main>Content here</main>
</div>
<footer class="fixed bottom-0 h-16">Footer</footer>

<!-- ❌ PROBLEM: Sticky footer not at bottom on short content -->
<body>
  <main>Short content</main>
  <footer>Footer floating in middle</footer>
</body>

<!-- ✅ FIX: Flexbox sticky footer -->
<body class="min-h-screen flex flex-col">
  <main class="flex-grow">Short content</main>
  <footer class="mt-auto">Footer at bottom</footer>
</body>
```

### 2. Screen Size Support Issues

#### Flutter Responsive Issues
```dart
// ❌ PROBLEM: Fixed width causes overflow on small screens
Container(
  width: 400, // Overflows on phones < 400px wide
  child: content,
)

// ✅ FIX: Use constraints or responsive sizing
Container(
  constraints: BoxConstraints(maxWidth: 400),
  width: double.infinity,
  child: content,
)

// ❌ PROBLEM: Row overflows on narrow screens
Row(
  children: [
    Icon(Icons.star),
    Text('Very long text that will overflow'),
    ElevatedButton(child: Text('Action')),
  ],
)

// ✅ FIX: Use Flexible/Expanded or Wrap
Row(
  children: [
    Icon(Icons.star),
    Expanded(
      child: Text('Very long text...', overflow: TextOverflow.ellipsis),
    ),
    ElevatedButton(child: Text('Action')),
  ],
)

// Or use Wrap for wrapping behavior
Wrap(
  spacing: 8,
  runSpacing: 8,
  children: [widgets],
)

// ❌ PROBLEM: No responsive layout for tablets/desktop
class MyScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ListView(...); // Same layout on all screen sizes
  }
}

// ✅ FIX: Use LayoutBuilder for responsive layouts
class MyScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth > 1200) {
          return _buildDesktopLayout();
        } else if (constraints.maxWidth > 600) {
          return _buildTabletLayout();
        } else {
          return _buildMobileLayout();
        }
      },
    );
  }
}
```

#### Web Responsive Issues
```html
<!-- ❌ PROBLEM: Fixed width breaks on mobile -->
<div style="width: 1200px">Content</div>

<!-- ✅ FIX: Use max-width with responsive classes -->
<div class="w-full max-w-7xl mx-auto px-4">Content</div>

<!-- ❌ PROBLEM: Horizontal scroll on mobile -->
<table class="w-full">
  <tr>
    <td>Col1</td><td>Col2</td><td>Col3</td><td>Col4</td><td>Col5</td>
  </tr>
</table>

<!-- ✅ FIX: Wrap table for horizontal scroll -->
<div class="overflow-x-auto">
  <table class="min-w-full">...</table>
</div>

<!-- ❌ PROBLEM: Sidebar always visible on mobile -->
<div class="flex">
  <aside class="w-64">Sidebar</aside>
  <main class="flex-1">Content</main>
</div>

<!-- ✅ FIX: Hide sidebar on mobile -->
<div class="flex">
  <aside class="hidden md:block w-64">Sidebar</aside>
  <main class="flex-1">Content</main>
</div>
```

### 3. CSS Validation Issues

#### Invalid or Ineffective CSS
```css
/* ❌ PROBLEM: Typos in property names */
.element {
  backround-color: red;  /* Typo: should be background-color */
  maring: 10px;          /* Typo: should be margin */
  trasition: all 0.3s;   /* Typo: should be transition */
}

/* ❌ PROBLEM: Invalid values */
.element {
  width: 100%;           /* Valid */
  width: 100vw;          /* Valid but may cause horizontal scroll */
  width: 100vw - 20px;   /* Invalid: needs calc() */
  width: calc(100vw - 20px); /* ✅ Correct */
}

/* ❌ PROBLEM: Conflicting properties */
.element {
  display: inline;
  width: 200px;          /* Ignored on inline elements */
  height: 100px;         /* Ignored on inline elements */
}

/* ✅ FIX: Use inline-block or block */
.element {
  display: inline-block;
  width: 200px;
  height: 100px;
}

/* ❌ PROBLEM: Z-index without position */
.element {
  z-index: 100;          /* Has no effect */
}

/* ✅ FIX: Add position */
.element {
  position: relative;
  z-index: 100;
}

/* ❌ PROBLEM: Percentage height without parent height */
.parent {
  /* No height specified */
}
.child {
  height: 50%;           /* Won't work - parent has no height */
}

/* ✅ FIX: Set parent height */
.parent {
  height: 100vh;         /* Or any defined height */
}
.child {
  height: 50%;
}
```

#### Tailwind CSS Issues
```html
<!-- ❌ PROBLEM: Conflicting classes -->
<div class="w-full w-64">Content</div>  <!-- w-full will win (last wins) -->
<div class="p-4 px-8">Content</div>     <!-- Confusing: px-8 overrides horizontal p-4 -->

<!-- ✅ FIX: Be explicit, avoid conflicts -->
<div class="w-64">Content</div>
<div class="py-4 px-8">Content</div>

<!-- ❌ PROBLEM: Missing responsive prefix -->
<div class="grid grid-cols-3">  <!-- 3 cols on ALL screen sizes -->
  <div>Item</div>
</div>

<!-- ✅ FIX: Mobile-first responsive -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
  <div>Item</div>
</div>

<!-- ❌ PROBLEM: Custom values without brackets -->
<div class="w-500">Content</div>  <!-- Invalid: not a Tailwind class -->

<!-- ✅ FIX: Use arbitrary values with brackets -->
<div class="w-[500px]">Content</div>

<!-- ❌ PROBLEM: Dark mode classes not working -->
<div class="bg-white dark:bg-black">  <!-- Won't work without dark mode config -->

<!-- ✅ FIX: Ensure tailwind.config.js has darkMode setting -->
// tailwind.config.js
module.exports = {
  darkMode: 'class', // or 'media'
  // ...
}
```

### 4. Overflow and Clipping Issues

#### Flutter Overflow
```dart
// ❌ PROBLEM: Text overflow without handling
Text(
  'Very long text that will overflow the container',
  // No overflow handling - will cause yellow/black stripes in debug
)

// ✅ FIX: Handle overflow
Text(
  'Very long text that will overflow the container',
  overflow: TextOverflow.ellipsis,
  maxLines: 2,
)

// ❌ PROBLEM: Column in unbounded height
ListView(
  children: [
    Column(  // Column has unbounded height inside ListView
      children: [
        Container(height: 1000),
      ],
    ),
  ],
)

// ✅ FIX: Avoid Column in ListView or use shrinkWrap
ListView(
  children: [
    Container(height: 1000), // Direct children, no Column needed
  ],
)

// Or wrap Column items
Column(
  mainAxisSize: MainAxisSize.min, // Use minimum space
  children: [...],
)

// ❌ PROBLEM: Image overflow
Image.network(
  'url',
  width: 500,  // May overflow container
)

// ✅ FIX: Constrain image
Container(
  constraints: BoxConstraints(maxWidth: double.infinity),
  child: Image.network(
    'url',
    fit: BoxFit.contain,
  ),
)
```

#### Web Overflow
```css
/* ❌ PROBLEM: Content overflows container */
.container {
  width: 300px;
}
.content {
  width: 500px;  /* Overflows parent */
}

/* ✅ FIX: Constrain content or handle overflow */
.container {
  width: 300px;
  overflow-x: auto;  /* Or hidden, scroll */
}
.content {
  max-width: 100%;
}

/* ❌ PROBLEM: Word breaks causing layout issues */
.text {
  /* Long URLs or words overflow */
}

/* ✅ FIX: Handle word breaking */
.text {
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}
```

### 5. Platform-Specific Issues

#### Flutter Platform Issues
```dart
// ❌ PROBLEM: Not handling notch/safe areas
return Scaffold(
  body: Container(
    child: content,  // Content under notch/status bar
  ),
);

// ✅ FIX: Use SafeArea
return Scaffold(
  body: SafeArea(
    child: Container(
      child: content,
    ),
  ),
);

// ❌ PROBLEM: Not handling system navigation bar (Android)
return Scaffold(
  extendBody: true,
  body: Container(
    decoration: BoxDecoration(image: backgroundImage),
    // Bottom of image hidden behind nav bar
  ),
);

// ✅ FIX: Account for system UI
return Scaffold(
  extendBody: true,
  extendBodyBehindAppBar: true,
  body: Container(
    padding: EdgeInsets.only(
      bottom: MediaQuery.of(context).padding.bottom,
    ),
    child: content,
  ),
);

// ❌ PROBLEM: iOS-style widgets on Android (or vice versa)
return CupertinoButton(  // Looks out of place on Android
  child: Text('Button'),
  onPressed: () {},
);

// ✅ FIX: Use adaptive widgets or platform checks
return Platform.isIOS
    ? CupertinoButton(child: Text('Button'), onPressed: () {})
    : ElevatedButton(child: Text('Button'), onPressed: () {});

// Or use Material widgets everywhere (recommended for consistency)
return ElevatedButton(
  child: Text('Button'),
  onPressed: () {},
);
```

#### Web Browser Issues
```html
<!-- ❌ PROBLEM: Safari flexbox bugs -->
<div class="flex">
  <img src="..." class="w-auto">  <!-- May not size correctly in Safari -->
</div>

<!-- ✅ FIX: Explicit dimensions for Safari -->
<div class="flex">
  <img src="..." class="w-auto min-w-0 max-w-full">
</div>

<!-- ❌ PROBLEM: iOS Safari viewport height -->
<div class="h-screen">  <!-- 100vh includes Safari toolbar -->
  Content
</div>

<!-- ✅ FIX: Use dvh (dynamic viewport height) or JS solution -->
<div class="h-[100dvh]">Content</div>

<!-- Or in CSS -->
<style>
  .full-height {
    height: 100vh;
    height: 100dvh;  /* Fallback for older browsers */
  }
</style>
```

## Debug Checklist

### Flutter Debug Checklist
```
Layout Issues:
[ ] No yellow/black overflow stripes in debug mode
[ ] SafeArea used where needed (top, bottom, left, right)
[ ] Bottom padding for FABs and bottom sheets
[ ] Keyboard doesn't cover input fields
[ ] All screen sizes work (use Device Preview)
[ ] Landscape orientation handled
[ ] Tablet/desktop layouts implemented

Widget Issues:
[ ] No unbounded height/width errors
[ ] Images have fit property set
[ ] Text has overflow handling
[ ] ListView/GridView have proper constraints
[ ] Nested scrollables have physics: NeverScrollableScrollPhysics()

Performance Issues:
[ ] const constructors used where possible
[ ] Keys used for dynamic lists
[ ] No rebuilds on every frame (check with DevTools)
```

### Web Debug Checklist
```
CSS Issues:
[ ] No CSS syntax errors (check DevTools console)
[ ] No conflicting Tailwind classes
[ ] All custom CSS properties valid
[ ] Z-index has matching position property
[ ] Percentage values have parent constraints

Responsive Issues:
[ ] Mobile viewport (320px-480px) works
[ ] Tablet viewport (768px-1024px) works
[ ] Desktop viewport (1024px+) works
[ ] No horizontal scroll on mobile
[ ] Touch targets large enough (44x44px min)

Browser Compatibility:
[ ] Chrome latest ✓
[ ] Firefox latest ✓
[ ] Safari latest ✓
[ ] Edge latest ✓
[ ] iOS Safari ✓
[ ] Chrome Android ✓
```

## Common Error Patterns

### Flutter Error Messages
```
// "A RenderFlex overflowed by X pixels"
→ Add Expanded/Flexible, or use SingleChildScrollView

// "Vertical viewport was given unbounded height"
→ Wrap ListView in Expanded, or set shrinkWrap: true

// "BoxConstraints forces an infinite width/height"
→ Add constraints to parent or use ConstrainedBox

// "setState() called after dispose()"
→ Check mounted before setState, cancel subscriptions in dispose()

// "Looking up a deactivated widget's ancestor"
→ Don't use context after async gap, store reference before await
```

### CSS Error Indicators
```
// Property has no effect (DevTools shows strikethrough)
→ Check display type compatibility

// Computed value is 'auto' when expecting a number
→ Set explicit dimensions on parent

// Element not visible but in DOM
→ Check visibility, opacity, display, height, overflow

// Hover/focus states not working
→ Check specificity, order of pseudo-classes
```

## Screen Size Breakpoints

### Flutter Breakpoints
```dart
class Breakpoints {
  static const double mobile = 600;
  static const double tablet = 900;
  static const double desktop = 1200;
  static const double largeDesktop = 1800;
}

// Usage with MediaQuery
final width = MediaQuery.of(context).size.width;
final isMobile = width < Breakpoints.mobile;
final isTablet = width >= Breakpoints.mobile && width < Breakpoints.tablet;
final isDesktop = width >= Breakpoints.tablet;

// Usage with LayoutBuilder
LayoutBuilder(
  builder: (context, constraints) {
    if (constraints.maxWidth < 600) return MobileLayout();
    if (constraints.maxWidth < 900) return TabletLayout();
    return DesktopLayout();
  },
)
```

### Tailwind Breakpoints
```
sm:  640px   → Small tablets, large phones landscape
md:  768px   → Tablets
lg:  1024px  → Small laptops, tablets landscape
xl:  1280px  → Laptops, desktops
2xl: 1536px  → Large desktops

// Usage
<div class="
  grid
  grid-cols-1      /* Mobile: 1 column */
  sm:grid-cols-2   /* 640px+: 2 columns */
  lg:grid-cols-3   /* 1024px+: 3 columns */
  xl:grid-cols-4   /* 1280px+: 4 columns */
">
```

### 6. Padding, Margins, and Alignment Issues

#### Spacing Consistency (8px Grid System)
```dart
// ❌ PROBLEM: Inconsistent spacing values
Padding(padding: EdgeInsets.all(13))  // Not on 8px grid
SizedBox(height: 17)                   // Not on 8px grid
Container(margin: EdgeInsets.only(left: 11))  // Not on 8px grid

// ✅ FIX: Use 8px grid system
Padding(padding: EdgeInsets.all(16))  // 8 * 2 = 16
SizedBox(height: 24)                   // 8 * 3 = 24
Container(margin: EdgeInsets.only(left: 8))  // 8 * 1 = 8

// Standard spacing scale (8px base):
// 4px  - Tight (related elements)
// 8px  - Default small
// 12px - Medium-small
// 16px - Default medium
// 24px - Large
// 32px - Section spacing
// 48px - Major sections
// 64px - Page sections
```

#### Flutter Alignment Issues
```dart
// ❌ PROBLEM: Misaligned items in Row
Row(
  children: [
    Icon(Icons.star),
    Text('Title'),  // Not vertically centered with icon
    Spacer(),
    Text('€100'),   // Different baseline than title
  ],
)

// ✅ FIX: Use crossAxisAlignment
Row(
  crossAxisAlignment: CrossAxisAlignment.center,
  children: [
    Icon(Icons.star),
    SizedBox(width: 8),  // Consistent spacing
    Text('Title'),
    Spacer(),
    Text('€100'),
  ],
)

// ❌ PROBLEM: Text not aligned in Column
Column(
  children: [
    Text('Short'),
    Text('Longer text here'),
    Text('Medium'),
  ],
)

// ✅ FIX: Set crossAxisAlignment
Column(
  crossAxisAlignment: CrossAxisAlignment.start,  // Left align
  children: [
    Text('Short'),
    Text('Longer text here'),
    Text('Medium'),
  ],
)

// ❌ PROBLEM: Card content not aligned
Card(
  child: Column(
    children: [
      Text('Title'),
      Text('Description'),
      ElevatedButton(child: Text('Action'), onPressed: () {}),
    ],
  ),
)

// ✅ FIX: Add padding and alignment
Card(
  child: Padding(
    padding: EdgeInsets.all(16),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,  // Full width
      children: [
        Text('Title', style: Theme.of(context).textTheme.titleLarge),
        SizedBox(height: 8),
        Text('Description'),
        SizedBox(height: 16),
        ElevatedButton(child: Text('Action'), onPressed: () {}),
      ],
    ),
  ),
)

// ❌ PROBLEM: ListTile content not vertically aligned
ListTile(
  leading: Container(
    width: 60,
    height: 60,
    child: Image.network('url'),
  ),
  title: Text('Title'),  // Not centered with large leading
)

// ✅ FIX: Use proper ListTile configuration
ListTile(
  leading: SizedBox(
    width: 56,
    height: 56,
    child: ClipRRect(
      borderRadius: BorderRadius.circular(8),
      child: Image.network('url', fit: BoxFit.cover),
    ),
  ),
  title: Text('Title'),
  subtitle: Text('Subtitle'),
  isThreeLine: false,
  contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
)

// ❌ PROBLEM: Form fields with inconsistent margins
Column(
  children: [
    TextField(decoration: InputDecoration(labelText: 'Name')),
    SizedBox(height: 10),
    TextField(decoration: InputDecoration(labelText: 'Email')),
    SizedBox(height: 20),  // Different spacing!
    TextField(decoration: InputDecoration(labelText: 'Phone')),
  ],
)

// ✅ FIX: Consistent field spacing
Column(
  children: [
    TextField(decoration: InputDecoration(labelText: 'Name')),
    SizedBox(height: 16),
    TextField(decoration: InputDecoration(labelText: 'Email')),
    SizedBox(height: 16),  // Same spacing
    TextField(decoration: InputDecoration(labelText: 'Phone')),
    SizedBox(height: 24),  // Larger before button
    ElevatedButton(...),
  ],
)
```

#### Web/Tailwind Alignment Issues
```html
<!-- ❌ PROBLEM: Inconsistent margin classes -->
<div class="mt-3">Item 1</div>
<div class="mt-5">Item 2</div>  <!-- Different margin! -->
<div class="mt-4">Item 3</div>  <!-- Different again! -->

<!-- ✅ FIX: Use space-y for consistent spacing -->
<div class="space-y-4">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>

<!-- ❌ PROBLEM: Items not vertically aligned in flex -->
<div class="flex">
  <span class="text-sm">Label</span>
  <span class="text-2xl">Value</span>  <!-- Different baseline -->
</div>

<!-- ✅ FIX: Use items-center or items-baseline -->
<div class="flex items-center gap-2">
  <span class="text-sm">Label</span>
  <span class="text-2xl">Value</span>
</div>

<!-- Or for text alignment -->
<div class="flex items-baseline gap-2">
  <span class="text-sm">Label</span>
  <span class="text-2xl">Value</span>
</div>

<!-- ❌ PROBLEM: Grid items not aligned -->
<div class="grid grid-cols-3">
  <div>Short</div>
  <div>Much longer content that wraps</div>
  <div>Medium text</div>
</div>

<!-- ✅ FIX: Use items-start/center/stretch -->
<div class="grid grid-cols-3 items-start gap-4">
  <div>Short</div>
  <div>Much longer content that wraps</div>
  <div>Medium text</div>
</div>

<!-- ❌ PROBLEM: Uneven padding on cards -->
<div class="p-4 md:p-6 lg:p-4">  <!-- Inconsistent responsive padding -->

<!-- ✅ FIX: Progressive responsive padding -->
<div class="p-4 md:p-6 lg:p-8">  <!-- Consistently increases -->

<!-- ❌ PROBLEM: Form labels and inputs not aligned -->
<form>
  <label class="block">Name</label>
  <input class="w-full">
  <label class="block mt-3">Email</label>  <!-- Different margin -->
  <input class="w-full">
</form>

<!-- ✅ FIX: Use consistent form structure -->
<form class="space-y-4">
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
    <input class="w-full rounded-md border-gray-300">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
    <input class="w-full rounded-md border-gray-300">
  </div>
</form>

<!-- ❌ PROBLEM: Table cells not aligned -->
<table>
  <tr>
    <td>Product</td>
    <td>€1,234.56</td>  <!-- Numbers should be right-aligned -->
  </tr>
</table>

<!-- ✅ FIX: Proper table alignment -->
<table class="w-full">
  <thead>
    <tr>
      <th class="text-left">Product</th>
      <th class="text-right">Amount</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="text-left">Product</td>
      <td class="text-right tabular-nums">€1,234.56</td>  <!-- Right-aligned numbers -->
    </tr>
  </tbody>
</table>
```

#### Margin vs Padding Rules
```
MARGIN (outside the element):
- Use for spacing BETWEEN elements
- Use for positioning element within parent
- Margins can collapse (be careful!)
- Use negative margins sparingly

PADDING (inside the element):
- Use for spacing INSIDE containers
- Use for clickable area expansion
- Use for text distance from borders
- Padding never collapses

// Flutter
Container(
  margin: EdgeInsets.all(16),   // Space outside
  padding: EdgeInsets.all(16),  // Space inside
  child: content,
)

// Tailwind
<div class="m-4 p-4">  <!-- m = margin outside, p = padding inside -->
```

#### Alignment Quick Reference

**Flutter Alignment:**
```dart
// MainAxisAlignment (along the main axis)
MainAxisAlignment.start       // Pack at start
MainAxisAlignment.end         // Pack at end
MainAxisAlignment.center      // Center items
MainAxisAlignment.spaceBetween // Even space between
MainAxisAlignment.spaceAround  // Even space around
MainAxisAlignment.spaceEvenly  // Equal space everywhere

// CrossAxisAlignment (perpendicular to main axis)
CrossAxisAlignment.start    // Align to start edge
CrossAxisAlignment.end      // Align to end edge
CrossAxisAlignment.center   // Center on cross axis
CrossAxisAlignment.stretch  // Stretch to fill
CrossAxisAlignment.baseline // Align text baselines
```

**Tailwind Alignment:**
```html
<!-- Flexbox -->
justify-start/end/center/between/around/evenly  <!-- Main axis -->
items-start/end/center/baseline/stretch          <!-- Cross axis -->

<!-- Grid -->
justify-items-start/end/center/stretch  <!-- Horizontal in cell -->
items-start/end/center/stretch          <!-- Vertical in cell -->
place-items-center                      <!-- Both axes -->

<!-- Self alignment -->
self-start/end/center/stretch  <!-- Individual item -->
```

### 7. Typography & Font Issues

#### Flutter Font Scaling & TextScaler
```dart
// ❌ DEPRECATED: textScaleFactor (removed in future Flutter versions)
final scale = MediaQuery.of(context).textScaleFactor;
Text('Hello', style: TextStyle(fontSize: 16 * scale));

// ✅ FIX: Use TextScaler (Android 14+ non-linear scaling support)
final textScaler = MediaQuery.of(context).textScaler;
Text('Hello', style: TextStyle(fontSize: textScaler.scale(16)));

// ❌ PROBLEM: Fixed font sizes don't adapt to screen size
Text('Title', style: TextStyle(fontSize: 24));  // Same on all devices

// ✅ FIX: Responsive font sizing with LayoutBuilder
LayoutBuilder(
  builder: (context, constraints) {
    final baseFontSize = constraints.maxWidth > 600 ? 24.0 : 18.0;
    return Text('Title', style: TextStyle(fontSize: baseFontSize));
  },
)

// ✅ FIX: Using flutter_screenutil for responsive fonts
Text('Title', style: TextStyle(fontSize: 24.sp));  // Scales with screen

// ❌ PROBLEM: AutoSizeText without constraints
AutoSizeText(
  'Long text that might overflow',
  // Missing maxLines, minFontSize
)

// ✅ FIX: Proper AutoSizeText configuration
AutoSizeText(
  'Long text that might overflow',
  maxLines: 2,
  minFontSize: 12,
  overflow: TextOverflow.ellipsis,
)

// ❌ PROBLEM: Font doesn't respect system accessibility settings
Text('Important', style: TextStyle(fontSize: 14));

// ✅ FIX: Use Theme-based text styles (respects accessibility)
Text('Important', style: Theme.of(context).textTheme.bodyMedium);
```

#### Tailwind CSS Fluid Typography
```html
<!-- ❌ PROBLEM: Fixed font sizes, not fluid -->
<h1 class="text-4xl">Title</h1>  <!-- Same size on all screens -->

<!-- ✅ FIX: Responsive font sizes with breakpoints -->
<h1 class="text-2xl md:text-3xl lg:text-4xl">Title</h1>

<!-- ✅ BETTER: Fluid typography with clamp() -->
<style>
  .fluid-title {
    font-size: clamp(1.5rem, 4vw, 3rem);  /* min, preferred, max */
  }
</style>
<h1 class="fluid-title">Title</h1>

<!-- Or in tailwind.config.js -->
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      fontSize: {
        'fluid-sm': 'clamp(0.875rem, 0.8rem + 0.25vw, 1rem)',
        'fluid-base': 'clamp(1rem, 0.9rem + 0.5vw, 1.25rem)',
        'fluid-lg': 'clamp(1.25rem, 1rem + 1vw, 1.75rem)',
        'fluid-xl': 'clamp(1.5rem, 1.2rem + 1.5vw, 2.25rem)',
        'fluid-2xl': 'clamp(2rem, 1.5rem + 2vw, 3rem)',
      }
    }
  }
}

<!-- ❌ PROBLEM: WCAG violation - can't scale to 200% -->
<p style="font-size: 10px; max-width: 100vw">
  Text using only viewport units can't scale properly
</p>

<!-- ✅ FIX: Combine rem with vw for accessibility -->
<p class="text-base" style="font-size: clamp(1rem, 0.9rem + 0.5vw, 1.25rem)">
  Accessible fluid text
</p>
```

#### Font Loading & Performance Issues
```html
<!-- ❌ PROBLEM: Flash of unstyled text (FOUT) -->
<link href="https://fonts.googleapis.com/css2?family=Inter" rel="stylesheet">

<!-- ✅ FIX: Font display swap -->
<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">

<!-- ✅ BETTER: Preload critical fonts -->
<link rel="preload" href="/fonts/inter.woff2" as="font" type="font/woff2" crossorigin>

<!-- CSS fallback -->
<style>
  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }
</style>
```

#### Flutter Font Configuration
```dart
// ❌ PROBLEM: Missing fallback fonts
TextStyle(fontFamily: 'CustomFont')  // Breaks if font not loaded

// ✅ FIX: Specify font family with fallbacks
TextStyle(
  fontFamily: 'CustomFont',
  fontFamilyFallback: ['Roboto', 'Arial', 'sans-serif'],
)

// ❌ PROBLEM: Font weights not loading
TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w600)  // May not work

// ✅ FIX: Ensure all weights are declared in pubspec.yaml
# pubspec.yaml
fonts:
  - family: Inter
    fonts:
      - asset: fonts/Inter-Regular.ttf
        weight: 400
      - asset: fonts/Inter-Medium.ttf
        weight: 500
      - asset: fonts/Inter-SemiBold.ttf
        weight: 600
      - asset: fonts/Inter-Bold.ttf
        weight: 700
```

### 8. Vue.js 3 Quirks & Debugging

#### Reactivity Issues
```javascript
// ❌ PROBLEM: Direct object mutation doesn't trigger reactivity
const state = reactive({ items: [] });
state.items = newItems;  // May not trigger update in some cases

// ✅ FIX: Use ref for replaceable values
const items = ref([]);
items.value = newItems;  // Always triggers

// ❌ PROBLEM: Computed property is stale
const fullName = computed(() => {
  return `${user.firstName} ${user.lastName}`;  // Not reactive if user is plain object
});

// ✅ FIX: Ensure source is reactive
const user = reactive({ firstName: '', lastName: '' });
const fullName = computed(() => `${user.firstName} ${user.lastName}`);

// ❌ PROBLEM: Watch not firing
watch(obj.property, (newVal) => {});  // Watching primitive, not reactive

// ✅ FIX: Use getter function
watch(() => obj.property, (newVal) => {});

// ❌ PROBLEM: Debugging reactive objects shows Proxy
console.log(state);  // Shows Proxy, hard to read

// ✅ FIX: Use toRaw or JSON stringify
import { toRaw } from 'vue';
console.log(toRaw(state));
console.log(JSON.parse(JSON.stringify(state)));
```

#### Directive Ordering Issues
```html
<!-- ❌ PROBLEM: v-if and v-for on same element -->
<div v-for="item in items" v-if="item.active">  <!-- v-if checked BEFORE v-for -->
  {{ item.name }}
</div>

<!-- ✅ FIX: Use template or computed property -->
<template v-for="item in items" :key="item.id">
  <div v-if="item.active">{{ item.name }}</div>
</template>

<!-- Or filter in computed -->
<div v-for="item in activeItems" :key="item.id">{{ item.name }}</div>
<script setup>
const activeItems = computed(() => items.value.filter(i => i.active));
</script>
```

#### Vue DevTools Tips
```javascript
// Enable component name in DevTools (production)
app.config.performance = true;

// Named components for better debugging
// ❌ Anonymous component
export default {
  setup() { ... }
}

// ✅ Named component
export default {
  name: 'MyComponent',
  setup() { ... }
}

// Or with script setup
<script setup>
defineOptions({ name: 'MyComponent' });
</script>
```

### 9. Inertia.js Quirks & Debugging

#### Invalid Inertia Response
```php
// ❌ PROBLEM: Non-Inertia response returned
public function show(Request $request, $id)
{
    if ($request->wantsJson()) {
        return response()->json($data);  // Breaks Inertia
    }
    return Inertia::render('Show', ['data' => $data]);
}

// ✅ FIX: Always return Inertia response
public function show(Request $request, $id)
{
    return Inertia::render('Show', ['data' => $data]);
}

// For API routes, use separate controller
```

#### State Preservation Issues
```javascript
// ❌ PROBLEM: Form state lost on navigation
router.visit(url);  // Resets component state

// ✅ FIX: Preserve state
router.visit(url, { preserveState: true });

// Or use Inertia form helper
const form = useForm({
  name: '',
  email: '',
});
form.post('/users', {
  preserveState: true,
  preserveScroll: true,
});
```

#### Persistent Layouts
```javascript
// ❌ PROBLEM: Layout re-renders on every page change
<template>
  <Layout>
    <slot />
  </Layout>
</template>

// ✅ FIX: Use persistent layouts
<script setup>
import Layout from './Layout.vue';
defineOptions({
  layout: Layout,
});
</script>

// Or in app.js
createInertiaApp({
  resolve: name => {
    const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
    page.then(module => {
      module.default.layout = module.default.layout || DefaultLayout;
    });
    return page;
  },
});
```

#### Shared Data Exposure
```php
// ❌ PROBLEM: Sensitive data in shared props (visible in page source)
Inertia::share([
    'auth' => [
        'user' => Auth::user(),  // Exposes all user attributes!
    ],
]);

// ✅ FIX: Only share necessary data
Inertia::share([
    'auth' => fn () => Auth::user() ? [
        'id' => Auth::user()->id,
        'name' => Auth::user()->name,
        'email' => Auth::user()->email,
    ] : null,
]);
```

### 10. JavaScript/TypeScript Quirks

#### Async/Await Issues
```javascript
// ❌ PROBLEM: forEach doesn't await
items.forEach(async (item) => {
  await processItem(item);  // Runs in parallel, not sequential
});

// ✅ FIX: Use for...of for sequential
for (const item of items) {
  await processItem(item);
}

// ✅ FIX: Use Promise.all for parallel
await Promise.all(items.map(item => processItem(item)));

// ❌ PROBLEM: Missing error handling
const data = await fetchData();  // Throws on error

// ✅ FIX: Try-catch or .catch()
try {
  const data = await fetchData();
} catch (error) {
  console.error('Fetch failed:', error);
}
```

#### This Binding Issues
```javascript
// ❌ PROBLEM: Lost 'this' in callbacks
class MyClass {
  constructor() {
    this.value = 42;
  }
  getValue() {
    setTimeout(function() {
      console.log(this.value);  // undefined!
    }, 100);
  }
}

// ✅ FIX: Use arrow function
getValue() {
  setTimeout(() => {
    console.log(this.value);  // 42
  }, 100);
}

// ✅ FIX: Or bind
getValue() {
  setTimeout(function() {
    console.log(this.value);
  }.bind(this), 100);
}
```

#### Type Coercion Gotchas
```javascript
// ❌ PROBLEM: Loose equality surprises
if (value == 0) { }        // true for "", [], false, null (sometimes)
if (value == '') { }       // true for 0, false, []

// ✅ FIX: Always use strict equality
if (value === 0) { }
if (value === '') { }

// ❌ PROBLEM: NaN comparison
if (value === NaN) { }     // Always false!

// ✅ FIX: Use Number.isNaN()
if (Number.isNaN(value)) { }

// ❌ PROBLEM: Array/object truthiness
if ([]) { }                // true (empty array is truthy!)
if ({}) { }                // true (empty object is truthy!)

// ✅ FIX: Check length or keys
if (array.length > 0) { }
if (Object.keys(obj).length > 0) { }
```

### 11. Flutter/Dart Quirks & Debugging

#### Memory Leaks
```dart
// ❌ PROBLEM: Controllers not disposed
class MyWidget extends StatefulWidget {
  @override
  _MyWidgetState createState() => _MyWidgetState();
}

class _MyWidgetState extends State<MyWidget> {
  final controller = TextEditingController();
  final scrollController = ScrollController();
  late AnimationController animController;
  StreamSubscription? subscription;

  @override
  void initState() {
    super.initState();
    subscription = stream.listen((data) => setState(() {}));
    // Controllers and subscriptions will leak!
  }
}

// ✅ FIX: Always dispose controllers and subscriptions
@override
void dispose() {
  controller.dispose();
  scrollController.dispose();
  animController.dispose();
  subscription?.cancel();
  super.dispose();
}
```

#### setState After Dispose
```dart
// ❌ PROBLEM: Crashes with "setState called after dispose"
Future<void> fetchData() async {
  final data = await api.getData();
  setState(() {
    this.data = data;  // Widget may be disposed!
  });
}

// ✅ FIX: Check mounted before setState
Future<void> fetchData() async {
  final data = await api.getData();
  if (mounted) {
    setState(() {
      this.data = data;
    });
  }
}
```

#### Const Constructor Benefits
```dart
// ❌ PROBLEM: Unnecessary rebuilds
ListView(
  children: [
    Container(
      padding: EdgeInsets.all(16),  // New instance every build
      child: Text('Hello'),
    ),
  ],
)

// ✅ FIX: Use const wherever possible
ListView(
  children: [
    Container(
      padding: const EdgeInsets.all(16),  // Const - reused
      child: const Text('Hello'),
    ),
  ],
)

// Or mark entire widget as const
const MyStaticWidget(),
```

#### BuildContext After Async
```dart
// ❌ PROBLEM: Using context after async gap
onPressed: () async {
  await saveData();
  Navigator.of(context).pop();  // Context may be invalid!
  ScaffoldMessenger.of(context).showSnackBar(...);  // May crash
}

// ✅ FIX: Store reference before async, check mounted
onPressed: () async {
  final navigator = Navigator.of(context);
  final messenger = ScaffoldMessenger.of(context);

  await saveData();

  if (mounted) {
    navigator.pop();
    messenger.showSnackBar(...);
  }
}
```

#### Debugging Tips
```dart
// Use debugPrint instead of print (handles long output)
debugPrint('Long debug message...');

// Break on caught exceptions
@pragma('vm:notify-debugger-on-exception')
void myFunction() {
  try {
    riskyOperation();
  } catch (e) {
    // Debugger will still break here
    handleError(e);
  }
}

// Inspect widget tree in debug mode
import 'package:flutter/rendering.dart';
debugPaintSizeEnabled = true;      // Show widget boundaries
debugPaintBaselinesEnabled = true;  // Show text baselines
debugPaintPointersEnabled = true;   // Show touch areas

// Performance debugging
Timeline.startSync('MyOperation');
// ... operation code ...
Timeline.finishSync();
```

### 12. CSS Quirks & Browser Issues

#### Safari-Specific Issues
```css
/* ❌ PROBLEM: 100vh includes Safari toolbar */
.full-height {
  height: 100vh;  /* Causes scroll on Safari mobile */
}

/* ✅ FIX: Use dvh (dynamic viewport height) */
.full-height {
  height: 100vh;
  height: 100dvh;  /* Modern browsers */
}

/* ❌ PROBLEM: Flexbox gaps not supported in older Safari */
.flex-container {
  gap: 1rem;  /* Not supported in Safari < 14.1 */
}

/* ✅ FIX: Use margin fallback */
.flex-container > * + * {
  margin-left: 1rem;
}

/* ❌ PROBLEM: aspect-ratio not supported */
.video {
  aspect-ratio: 16 / 9;  /* Safari < 15 */
}

/* ✅ FIX: Padding-bottom trick */
.video-wrapper {
  position: relative;
  padding-bottom: 56.25%;  /* 9/16 = 0.5625 */
}
.video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
```

#### Flexbox Issues
```css
/* ❌ PROBLEM: flex-basis: auto issues */
.flex-item {
  flex: 1;  /* May behave differently across browsers */
}

/* ✅ FIX: Be explicit */
.flex-item {
  flex: 1 1 0%;  /* grow, shrink, basis */
}

/* ❌ PROBLEM: min-width auto in flex */
.flex-item {
  /* Content can overflow flex container */
}

/* ✅ FIX: Set min-width */
.flex-item {
  min-width: 0;  /* Allow shrinking below content size */
}
```

## Dutch Bookkeeping App Examples

### Example 1: Invoice List Overflow (Flutter)
```dart
// ❌ PROBLEM: Invoice list items overflow on small screens
ListView(
  children: invoices.map((invoice) =>
    Row(
      children: [
        Text('${invoice.number}'),
        Text('${invoice.clientName}'), // Long names overflow
        Text('€ ${invoice.amount}'),
        ElevatedButton(child: Text('Details'), onPressed: () {}),
      ],
    )
  ).toList(),
)

// ✅ FIX: Use Expanded and overflow handling
ListView(
  children: invoices.map((invoice) =>
    Padding(
      padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(
              invoice.number,
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          Expanded(
            child: Text(
              invoice.clientName,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          SizedBox(width: 8),
          Text(
            '€ ${invoice.amount.toStringAsFixed(2)}',
            style: TextStyle(fontFamily: 'monospace'), // Tabular figures
          ),
          SizedBox(width: 8),
          IconButton(
            icon: Icon(Icons.arrow_forward),
            onPressed: () => viewDetails(invoice),
          ),
        ],
      ),
    )
  ).toList(),
)
```

### Example 2: VAT Declaration Form Layout (Web)
```html
<!-- ❌ PROBLEM: VAT form doesn't fit on mobile -->
<div class="grid grid-cols-3 gap-4">
  <div>
    <label>Omzet hoog tarief (21%)</label>
    <input type="number" />
  </div>
  <div>
    <label>Omzet laag tarief (9%)</label>
    <input type="number" />
  </div>
  <div>
    <label>Omzet 0% / vrijgesteld</label>
    <input type="number" />
  </div>
</div>

<!-- ✅ FIX: Responsive grid with proper breakpoints -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      Omzet hoog tarief (21%)
    </label>
    <div class="relative">
      <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
        €
      </span>
      <input
        type="number"
        class="pl-7 block w-full rounded-md border-gray-300"
        placeholder="0,00"
      />
    </div>
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      Omzet laag tarief (9%)
    </label>
    <div class="relative">
      <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
        €
      </span>
      <input
        type="number"
        class="pl-7 block w-full rounded-md border-gray-300"
        placeholder="0,00"
      />
    </div>
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      Omzet 0% / vrijgesteld
    </label>
    <div class="relative">
      <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
        €
      </span>
      <input
        type="number"
        class="pl-7 block w-full rounded-md border-gray-300"
        placeholder="0,00"
      />
    </div>
  </div>
</div>
```

### Example 3: Company Selector Dropdown (Vue/Inertia)
```vue
<!-- ❌ PROBLEM: Long company names break layout -->
<template>
  <select v-model="selectedCompany" class="w-64">
    <option v-for="company in companies" :value="company.id">
      {{ company.name }} ({{ company.kvk }})
    </option>
  </select>
</template>

<!-- ✅ FIX: Responsive dropdown with truncation -->
<template>
  <Listbox v-model="selectedCompany" as="div" class="relative">
    <ListboxButton class="
      relative w-full cursor-default rounded-md border border-gray-300
      bg-white py-2 pl-3 pr-10 text-left shadow-sm
      focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500
      sm:text-sm
    ">
      <span class="block truncate">
        {{ selectedCompany?.name || 'Selecteer bedrijf' }}
      </span>
      <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
        <ChevronUpDownIcon class="h-5 w-5 text-gray-400" />
      </span>
    </ListboxButton>

    <transition
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <ListboxOptions class="
        absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md
        bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5
        focus:outline-none sm:text-sm
      ">
        <ListboxOption
          v-for="company in companies"
          :key="company.id"
          :value="company"
          as="template"
          v-slot="{ active, selected }"
        >
          <li :class="[
            active ? 'bg-blue-600 text-white' : 'text-gray-900',
            'relative cursor-default select-none py-2 pl-3 pr-9'
          ]">
            <div class="flex flex-col">
              <span :class="[selected ? 'font-semibold' : 'font-normal', 'truncate']">
                {{ company.name }}
              </span>
              <span :class="[active ? 'text-blue-200' : 'text-gray-500', 'text-xs']">
                KVK: {{ company.kvk }}
              </span>
            </div>
            <span
              v-if="selected"
              :class="[
                active ? 'text-white' : 'text-blue-600',
                'absolute inset-y-0 right-0 flex items-center pr-4'
              ]"
            >
              <CheckIcon class="h-5 w-5" />
            </span>
          </li>
        </ListboxOption>
      </ListboxOptions>
    </transition>
  </Listbox>
</template>
```

## Troubleshooting Common Issues

### Problem 1: Invoice PDF Generation Shows Euro Symbol as "?"

**Symptoms:**
```
Invoice PDF displays: "? 1.234,56" instead of "€ 1.234,56"
```

**Root Cause:** Font doesn't support Euro symbol (€)

**Solution:**
```dart
// Flutter PDF generation
import 'package:pdf/widgets.dart' as pw;

// ❌ BAD: Using default font without Euro support
final pdf = pw.Document();
pdf.addPage(
  pw.Page(
    build: (context) => pw.Text('€ 1.234,56'),
  ),
);

// ✅ GOOD: Load font with Euro symbol support
final euroFont = await rootBundle.load('fonts/Roboto-Regular.ttf');
final ttf = pw.Font.ttf(euroFont);

final pdf = pw.Document();
pdf.addPage(
  pw.Page(
    build: (context) => pw.Text(
      '€ 1.234,56',
      style: pw.TextStyle(font: ttf),
    ),
  ),
);

// Or for web (Tailwind CSS)
// Ensure font-family supports Euro symbol
<span class="font-sans">€ 1.234,56</span> <!-- Uses system font with Euro -->
```

### Problem 2: Number Formatting Breaks for Large Amounts

**Symptoms:**
```
Display: "1234567.89" instead of "€ 1.234.567,89" (Dutch format)
```

**Root Cause:** Using default number formatting instead of Dutch locale

**Solution:**
```dart
// Flutter: Use NumberFormat with Dutch locale
import 'package:intl/intl.dart';

// ❌ BAD: No formatting
Text('€ ${invoice.total}') // Displays: € 1234567.89

// ✅ GOOD: Dutch number formatting
final formatter = NumberFormat.currency(
  locale: 'nl_NL',
  symbol: '€',
  decimalDigits: 2,
);
Text(formatter.format(invoice.total)) // Displays: € 1.234.567,89

// Web (Vue/JavaScript)
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount);
};

// Usage
<span>{{ formatCurrency(invoice.total) }}</span> // € 1.234.567,89
```

### Problem 3: Date Picker Shows Wrong Format

**Symptoms:**
```
User expects: "15-01-2025" (DD-MM-YYYY Dutch format)
Shows: "01/15/2025" (MM/DD/YYYY US format)
```

**Root Cause:** Using default locale instead of Dutch

**Solution:**
```dart
// Flutter: Configure date format
import 'package:intl/intl.dart';

// ❌ BAD: US format
Text(DateFormat('MM/dd/yyyy').format(invoice.date))

// ✅ GOOD: Dutch format
Text(DateFormat('dd-MM-yyyy', 'nl_NL').format(invoice.date))

// Or use localized medium format
Text(DateFormat.yMMMd('nl_NL').format(invoice.date)) // 15 jan. 2025

// Web (Vue) - Set up global date formatting
import { format } from 'date-fns';
import { nl } from 'date-fns/locale';

const formatDate = (date) => {
  return format(new Date(date), 'dd-MM-yyyy', { locale: nl });
};
```

### Problem 4: Bottom Navigation Covers FAB on Android

**Symptoms:**
```
FAB (Floating Action Button) for "Nieuwe factuur" is partially hidden
behind bottom navigation bar
```

**Solution:**
```dart
// ❌ BAD: FAB placement without bottom padding
Scaffold(
  body: InvoiceList(),
  floatingActionButton: FloatingActionButton(
    onPressed: () => createInvoice(),
    child: Icon(Icons.add),
  ),
  bottomNavigationBar: BottomNavigationBar(...),
)

// ✅ GOOD: Add bottom padding or use inset FAB
Scaffold(
  body: InvoiceList(),
  floatingActionButton: Padding(
    padding: EdgeInsets.only(bottom: 16), // Space above nav bar
    child: FloatingActionButton.extended(
      onPressed: () => createInvoice(),
      icon: Icon(Icons.add),
      label: Text('Nieuwe factuur'),
    ),
  ),
  floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
  bottomNavigationBar: BottomNavigationBar(
    items: [
      BottomNavigationBarItem(icon: Icon(Icons.dashboard), label: 'Dashboard'),
      BottomNavigationBarItem(icon: Icon(Icons.receipt), label: 'Facturen'),
      BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Relaties'),
    ],
  ),
)
```

### Problem 5: Table Horizontal Scroll Not Working on Mobile

**Symptoms:**
```
Invoice table extends beyond screen width but can't scroll horizontally
```

**Solution:**
```html
<!-- ❌ BAD: No scroll container -->
<table class="min-w-full">
  <thead>
    <tr>
      <th>Factuurnummer</th>
      <th>Klant</th>
      <th>Bedrag</th>
      <th>Vervaldatum</th>
      <th>Status</th>
      <th>Acties</th>
    </tr>
  </thead>
  <tbody>
    <!-- Rows -->
  </tbody>
</table>

<!-- ✅ GOOD: Wrap in overflow container -->
<div class="overflow-x-auto -mx-4 sm:mx-0">
  <div class="inline-block min-w-full align-middle">
    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 whitespace-nowrap">
              Factuurnummer
            </th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 whitespace-nowrap">
              Klant
            </th>
            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 whitespace-nowrap">
              Bedrag
            </th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 whitespace-nowrap">
              Vervaldatum
            </th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 whitespace-nowrap">
              Status
            </th>
            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 whitespace-nowrap">
              <span class="sr-only">Acties</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <!-- Rows -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Mobile: Show cards instead of table -->
<div class="sm:hidden space-y-4">
  <div v-for="invoice in invoices" class="bg-white p-4 rounded-lg shadow">
    <div class="flex justify-between items-start">
      <div>
        <p class="font-semibold">{{ invoice.number }}</p>
        <p class="text-sm text-gray-600">{{ invoice.clientName }}</p>
      </div>
      <StatusBadge :status="invoice.status" />
    </div>
    <div class="mt-2 flex justify-between items-center">
      <span class="text-lg font-bold">{{ formatCurrency(invoice.total) }}</span>
      <span class="text-sm text-gray-500">Vervalt: {{ formatDate(invoice.dueDate) }}</span>
    </div>
  </div>
</div>
```

## Best Practices

### 1. **Use Dutch Locale for All Formatting**
Always configure `nl_NL` locale for numbers, dates, and currency to match user expectations.

```dart
// Flutter: Set default locale in main.dart
import 'package:intl/intl_standalone.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await findSystemLocale(); // Sets to device locale
  Intl.defaultLocale = 'nl_NL'; // Force Dutch
  runApp(MyApp());
}

// Web: Configure in app.js
import { createInertiaApp } from '@inertiajs/vue3';
import { setDefaultOptions } from 'date-fns';
import { nl } from 'date-fns/locale';

setDefaultOptions({ locale: nl });
```

### 2. **Test on Real Devices, Not Just Emulators**
Emulators don't always show real-world layout issues (notches, different screen densities).

```bash
# Flutter: Test on multiple screen sizes
flutter run -d <device-id>

# Use Device Preview package for quick testing
DevicePreview(
  enabled: !kReleaseMode,
  builder: (context) => MyApp(),
)
```

### 3. **Use Consistent Spacing (8px Grid System)**
All margins and paddings should be multiples of 8px for visual harmony.

```dart
// Flutter
EdgeInsets.all(8)    // ✓
EdgeInsets.all(16)   // ✓
EdgeInsets.all(24)   // ✓
EdgeInsets.all(15)   // ✗ Not on 8px grid

// Web (Tailwind)
class="p-2"   // 8px  ✓
class="p-4"   // 16px ✓
class="p-6"   // 24px ✓
class="p-5"   // 20px ✗ Not on 8px grid (use p-6 instead)
```

### 4. **Handle Keyboard Overlap on Forms**
Always ensure input fields aren't hidden when keyboard appears.

```dart
// Flutter: Wrap forms in SingleChildScrollView
Scaffold(
  resizeToAvoidBottomInset: true,
  body: SafeArea(
    child: SingleChildScrollView(
      padding: EdgeInsets.all(16),
      child: Column(
        children: [
          // Form fields
          TextField(decoration: InputDecoration(labelText: 'Bedrijfsnaam')),
          TextField(decoration: InputDecoration(labelText: 'KVK-nummer')),
          SizedBox(height: MediaQuery.of(context).viewInsets.bottom), // Extra space when keyboard open
        ],
      ),
    ),
  ),
)
```

### 5. **Provide Adequate Touch Targets**
Minimum 44x44px for mobile, especially for elderly users (common in bookkeeping).

```dart
// Flutter
IconButton(
  iconSize: 24,
  padding: EdgeInsets.all(12), // Makes total touch area 48x48
  icon: Icon(Icons.delete),
  onPressed: () => delete(),
)

// Web (Tailwind)
<button class="p-3 min-h-[44px] min-w-[44px] flex items-center justify-center">
  <TrashIcon class="h-5 w-5" />
</button>
```

### 6. **Show Loading States for All Async Operations**
Never leave user wondering if app is working.

```dart
// Flutter
if (isLoading)
  Center(child: CircularProgressIndicator())
else if (hasError)
  ErrorView(error: error, onRetry: loadData)
else if (data.isEmpty)
  EmptyState(message: 'Geen facturen gevonden')
else
  InvoiceList(invoices: data)
```

### 7. **Use Semantic Colors for Financial Data**
- Green: Positive amounts, paid invoices
- Red: Negative amounts, overdue invoices
- Amber: Pending, warnings
- Blue: Information, neutral

```dart
Color getAmountColor(double amount) {
  if (amount > 0) return Colors.green;
  if (amount < 0) return Colors.red;
  return Colors.grey;
}
```

## Anti-Patterns to Avoid

### ❌ Anti-Pattern 1: Hardcoded Strings Instead of Localization

```dart
// ❌ BAD
Text('Invoice')
Text('Total: € ${amount}')

// ✅ GOOD
Text(AppLocalizations.of(context).invoice)
Text('${AppLocalizations.of(context).total}: ${formatCurrency(amount)}')
```

### ❌ Anti-Pattern 2: Fixed Widths Breaking Responsive Design

```dart
// ❌ BAD: Fixed width
Container(
  width: 400, // Breaks on narrow screens
  child: InvoiceForm(),
)

// ✅ GOOD: Constrained max width
Container(
  constraints: BoxConstraints(maxWidth: 600),
  width: double.infinity,
  padding: EdgeInsets.all(16),
  child: InvoiceForm(),
)
```

### ❌ Anti-Pattern 3: Not Handling Empty/Error States

```dart
// ❌ BAD: Shows blank screen
ListView.builder(
  itemCount: invoices.length,
  itemBuilder: (context, index) => InvoiceCard(invoices[index]),
)

// ✅ GOOD: Handle all states
if (isLoading) {
  return Center(child: CircularProgressIndicator());
} else if (error != null) {
  return ErrorView(error: error);
} else if (invoices.isEmpty) {
  return EmptyState(
    icon: Icons.receipt,
    message: 'Nog geen facturen',
    action: ElevatedButton(
      child: Text('Maak eerste factuur'),
      onPressed: createInvoice,
    ),
  );
} else {
  return ListView.builder(...);
}
```

### ❌ Anti-Pattern 4: Ignoring Platform Differences

```dart
// ❌ BAD: Same UI on Android and iOS
AppBar(
  title: Text('Facturen'),
  actions: [IconButton(...)],
)

// ✅ GOOD: Platform-adaptive
Platform.isIOS
  ? CupertinoNavigationBar(
      middle: Text('Facturen'),
      trailing: CupertinoButton(...)
    )
  : AppBar(
      title: Text('Facturen'),
      actions: [IconButton(...)],
    )

// Or use adaptive widgets
import 'package:flutter/material.dart' show Theme;
import 'package:flutter/cupertino.dart' show CupertinoTheme;
```

## Integration with Other Skills

### With `ui-ux-expert.md`
- Use UX principles for layout decisions
- Follow accessibility guidelines
- Implement consistent design patterns

### With `flutter-app-design.md`
- Apply Material 3 theming
- Use responsive layout patterns
- Implement platform-adaptive widgets

### With `webdesign.md`
- Maintain consistency between web and mobile
- Share color schemes and typography
- Use same breakpoints where applicable

### With `dutch-tax-compliance.md`
- Ensure VAT display follows Dutch regulations
- Format amounts according to Dutch standards
- Use correct date formats for official documents

### With `testing-expert.md`
- Write widget tests for layout edge cases
- Test responsive breakpoints
- Verify overflow handling

## Pre-Launch Checklist

### Mobile (Flutter)
- [ ] No yellow/black overflow indicators in debug mode
- [ ] All screens tested on smallest device (iPhone SE: 375x667)
- [ ] All screens tested on largest device (iPad Pro: 1024x1366)
- [ ] Safe areas respected (notch, status bar, home indicator)
- [ ] Keyboard doesn't cover input fields
- [ ] FAB doesn't overlap content
- [ ] All text has overflow handling
- [ ] Loading states for all async operations
- [ ] Error states with retry actions
- [ ] Empty states with helpful messages
- [ ] Touch targets minimum 44x44px
- [ ] Dark mode tested (if supported)

### Web (Vue/Tailwind)
- [ ] Tested on mobile (320px-480px)
- [ ] Tested on tablet (768px-1024px)
- [ ] Tested on desktop (1280px+)
- [ ] No horizontal scroll on mobile
- [ ] Tables have horizontal scroll or responsive alternative
- [ ] Forms work on mobile keyboards
- [ ] All interactive elements have focus states
- [ ] Tested on Chrome, Firefox, Safari, Edge
- [ ] Print styles (for invoices/reports)
- [ ] Works without JavaScript (progressive enhancement)

## Debug Tools
```dart
// Show layout boundaries
debugPaintSizeEnabled = true;

// Show baseline alignments
debugPaintBaselinesEnabled = true;

// Show pointer tap areas
debugPaintPointersEnabled = true;

// In main.dart for debugging
void main() {
  debugPaintSizeEnabled = false; // Set true to debug
  runApp(MyApp());
}

// Device Preview package for testing screen sizes
DevicePreview(
  enabled: !kReleaseMode,
  builder: (context) => MyApp(),
)
```

### Browser Debug Tools
```javascript
// Show all elements with outlines
document.querySelectorAll('*').forEach(el => {
  el.style.outline = '1px solid red';
});

// Find elements causing horizontal scroll
document.querySelectorAll('*').forEach(el => {
  if (el.scrollWidth > el.clientWidth) {
    console.log('Overflow:', el);
  }
});

// Check computed styles
getComputedStyle(element).property;

// Tailwind CSS debug (add to config)
// tailwind.config.js
module.exports = {
  plugins: [
    require('tailwindcss-debug-screens'),
  ],
}
```

## Response Format

When debugging frontend issues, structure your response as:

```markdown
# Frontend Debug Report: [File/Component]

## Summary
- **File:** [path]
- **Type:** [Flutter Widget / Vue Component / Blade View]
- **Issues Found:** X critical, Y major, Z minor

## Critical Issues (Causes Broken UI)

### Issue 1: [Description]
**Line:** X
**Problem:** [What's wrong]
**Impact:** [What breaks]

**Before:**
```[language]
[problematic code]
```

**After:**
```[language]
[fixed code]
```

## Major Issues (Visual Problems)

### Issue 1: [Description]
...

## Minor Issues (Improvements)

### Issue 1: [Description]
...

## Screen Size Compatibility

| Screen Size | Status | Issues |
|-------------|--------|--------|
| Mobile (<600px) | ✅/❌ | [issues] |
| Tablet (600-900px) | ✅/❌ | [issues] |
| Desktop (>900px) | ✅/❌ | [issues] |

## Bottom Margin Check
- [ ] Content not hidden behind bottom nav
- [ ] FAB doesn't overlap content
- [ ] Keyboard doesn't cover inputs
- [ ] Safe areas respected

## CSS/Style Validation
- [ ] No invalid properties
- [ ] No conflicting classes
- [ ] All values are valid
- [ ] Responsive classes correct
```

---

## ENHANCED: Automated Frontend Testing Integration

### Cypress E2E Tests for Layout Validation

```javascript
// cypress/e2e/layout-validation.cy.js

describe('Layout Validation - Dutch Bookkeeping App', () => {
  beforeEach(() => {
    cy.login('admin@boekhouder.nl', 'password')
    cy.selectCompany('Test BV')
  })

  it('should have no visual regressions on invoice list', () => {
    cy.visit('/invoices')

    // Wait for page to fully load
    cy.get('[data-testid="invoice-list"]').should('be.visible')

    // Check no horizontal overflow
    cy.window().then(win => {
      expect(win.document.documentElement.scrollWidth)
        .to.equal(win.document.documentElement.clientWidth)
    })

    // Check responsive breakpoints
    const viewports = ['iphone-6', 'ipad-2', [1920, 1080]]

    viewports.forEach(viewport => {
      cy.viewport(viewport)
      cy.get('[data-testid="invoice-list"]').should('be.visible')
      cy.get('[data-testid="invoice-item"]').first().should('be.visible')
    })

    // Visual regression test
    cy.matchImageSnapshot('invoice-list')
  })

  it('should format Dutch currency correctly', () => {
    cy.visit('/invoices/1')

    // Check Euro symbol
    cy.get('[data-testid="invoice-total"]').should('contain', '€')

    // Check Dutch number formatting (1.234,56)
    cy.get('[data-testid="invoice-total"]')
      .invoke('text')
      .should('match', /€\s?\d{1,3}(\.\d{3})*(,\d{2})?/)
  })

  it('should handle long company names without overflow', () => {
    cy.visit('/companies')

    cy.get('[data-testid="company-name"]').each($el => {
      const el = $el[0]

      // Check text doesn't overflow container
      expect(el.scrollWidth).to.be.lte(el.offsetWidth + 1) // +1 for rounding
    })
  })

  it('should have minimum touch targets on mobile', () => {
    cy.viewport('iphone-6')
    cy.visit('/invoices')

    cy.get('button, a, [role="button"]').each($el => {
      const rect = $el[0].getBoundingClientRect()

      // Minimum 44x44px touch target (Apple HIG)
      expect(rect.width).to.be.at.least(44)
      expect(rect.height).to.be.at.least(44)
    })
  })
})
```

### Visual Regression Testing with Percy

```javascript
// cypress/e2e/visual-regression.cy.js

describe('Visual Regression Tests', () => {
  const pages = [
    '/dashboard',
    '/invoices',
    '/invoices/create',
    '/expenses',
    '/reports/vat',
    '/settings/company',
  ]

  const breakpoints = [
    { name: 'mobile', width: 375, height: 667 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'desktop', width: 1920, height: 1080 },
  ]

  pages.forEach(page => {
    breakpoints.forEach(({ name, width, height }) => {
      it(`${page} should match snapshot on ${name}`, () => {
        cy.viewport(width, height)
        cy.visit(page)

        // Wait for page to stabilize
        cy.wait(500)

        // Take Percy snapshot
        cy.percySnapshot(`${page}-${name}`)
      })
    })
  })
})
```

---

## ENHANCED: Performance Debugging Tools

### Frontend Performance Profiler

```javascript
// resources/js/utils/performance-profiler.js

class PerformanceProfiler {
  constructor() {
    this.marks = new Map()
    this.measures = new Map()
  }

  /**
   * Start measuring performance
   */
  start(name) {
    const markName = `${name}-start`
    performance.mark(markName)
    this.marks.set(name, markName)
  }

  /**
   * End measurement and log results
   */
  end(name, threshold = 1000) {
    const startMark = this.marks.get(name)
    if (!startMark) {
      console.warn(`No start mark found for: ${name}`)
      return
    }

    const endMark = `${name}-end`
    performance.mark(endMark)

    const measureName = `measure-${name}`
    performance.measure(measureName, startMark, endMark)

    const measure = performance.getEntriesByName(measureName)[0]
    const duration = measure.duration

    // Log if exceeds threshold
    if (duration > threshold) {
      console.warn(`⚠️ Performance issue: ${name} took ${duration.toFixed(2)}ms (threshold: ${threshold}ms)`)
    } else {
      console.log(`✅ ${name}: ${duration.toFixed(2)}ms`)
    }

    this.measures.set(name, duration)

    // Clean up
    performance.clearMarks(startMark)
    performance.clearMarks(endMark)
    performance.clearMeasures(measureName)

    return duration
  }

  /**
   * Get all measurements
   */
  getReport() {
    return Object.fromEntries(this.measures)
  }

  /**
   * Measure component render time
   */
  measureComponent(componentName, renderFn) {
    this.start(componentName)
    const result = renderFn()
    this.end(componentName)
    return result
  }

  /**
   * Measure API call duration
   */
  async measureAPI(apiName, apiCall) {
    this.start(apiName)
    try {
      const result = await apiCall()
      this.end(apiName)
      return result
    } catch (error) {
      this.end(apiName)
      throw error
    }
  }
}

export default new PerformanceProfiler()
```

### Usage in Vue Components

```vue
<script setup>
import { onMounted } from 'vue'
import profiler from '@/utils/performance-profiler'

onMounted(() => {
  profiler.start('invoice-list-render')

  // ... component logic ...

  profiler.end('invoice-list-render', 500) // Warn if > 500ms
})

// Measure API calls
const fetchInvoices = async () => {
  return profiler.measureAPI('fetch-invoices', async () => {
    return await axios.get('/api/invoices')
  })
}
</script>
```

---

## ENHANCED: Accessibility (A11Y) Debugging

### A11Y Audit Tool

```javascript
// resources/js/utils/a11y-auditor.js

class A11yAuditor {
  /**
   * Run comprehensive accessibility audit
   */
  audit() {
    const issues = []

    // Check for missing alt text on images
    issues.push(...this.checkImageAltText())

    // Check for sufficient color contrast
    issues.push(...this.checkColorContrast())

    // Check for keyboard accessibility
    issues.push(...this.checkKeyboardAccessibility())

    // Check for ARIA labels
    issues.push(...this.checkAriaLabels())

    // Check for form labels
    issues.push(...this.checkFormLabels())

    // Check for heading hierarchy
    issues.push(...this.checkHeadingHierarchy())

    return {
      totalIssues: issues.length,
      critical: issues.filter(i => i.severity === 'critical').length,
      warning: issues.filter(i => i.severity === 'warning').length,
      issues,
    }
  }

  checkImageAltText() {
    const issues = []
    const images = document.querySelectorAll('img')

    images.forEach((img, index) => {
      if (!img.alt || img.alt.trim() === '') {
        issues.push({
          severity: 'critical',
          type: 'missing-alt-text',
          element: img,
          message: `Image #${index + 1} missing alt text`,
          fix: 'Add descriptive alt attribute to image',
        })
      }
    })

    return issues
  }

  checkColorContrast() {
    const issues = []
    const textElements = document.querySelectorAll('p, span, a, button, h1, h2, h3, h4, h5, h6')

    textElements.forEach(el => {
      const style = window.getComputedStyle(el)
      const fgColor = this.parseColor(style.color)
      const bgColor = this.parseColor(style.backgroundColor) || { r: 255, g: 255, b: 255 }

      const contrast = this.calculateContrast(fgColor, bgColor)
      const fontSize = parseFloat(style.fontSize)

      // WCAG AA requires 4.5:1 for normal text, 3:1 for large text (18pt+)
      const requiredContrast = fontSize >= 18 ? 3 : 4.5

      if (contrast < requiredContrast) {
        issues.push({
          severity: 'warning',
          type: 'insufficient-contrast',
          element: el,
          message: `Low contrast ratio: ${contrast.toFixed(2)}:1 (required: ${requiredContrast}:1)`,
          fix: 'Increase color contrast between text and background',
        })
      }
    })

    return issues
  }

  checkKeyboardAccessibility() {
    const issues = []
    const interactiveElements = document.querySelectorAll('div[onclick], span[onclick]')

    interactiveElements.forEach(el => {
      if (!el.hasAttribute('tabindex') && !el.hasAttribute('role')) {
        issues.push({
          severity: 'critical',
          type: 'keyboard-inaccessible',
          element: el,
          message: 'Interactive element not keyboard accessible',
          fix: 'Add tabindex="0" and role="button" or use <button> element',
        })
      }
    })

    return issues
  }

  checkAriaLabels() {
    const issues = []
    const buttons = document.querySelectorAll('button:not([aria-label])')

    buttons.forEach(btn => {
      if (!btn.textContent.trim() && !btn.querySelector('img[alt]')) {
        issues.push({
          severity: 'critical',
          type: 'missing-aria-label',
          element: btn,
          message: 'Button has no accessible label',
          fix: 'Add aria-label or text content to button',
        })
      }
    })

    return issues
  }

  checkFormLabels() {
    const issues = []
    const inputs = document.querySelectorAll('input:not([type="hidden"]), select, textarea')

    inputs.forEach(input => {
      const id = input.id
      const hasLabel = id && document.querySelector(`label[for="${id}"]`)
      const hasAriaLabel = input.hasAttribute('aria-label') || input.hasAttribute('aria-labelledby')

      if (!hasLabel && !hasAriaLabel) {
        issues.push({
          severity: 'critical',
          type: 'missing-form-label',
          element: input,
          message: 'Form field has no associated label',
          fix: 'Add <label> element or aria-label attribute',
        })
      }
    })

    return issues
  }

  checkHeadingHierarchy() {
    const issues = []
    const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6')
    let lastLevel = 0

    headings.forEach(heading => {
      const level = parseInt(heading.tagName[1])

      if (lastLevel !== 0 && level > lastLevel + 1) {
        issues.push({
          severity: 'warning',
          type: 'heading-hierarchy-skip',
          element: heading,
          message: `Heading level skipped from h${lastLevel} to h${level}`,
          fix: 'Maintain proper heading hierarchy (don\'t skip levels)',
        })
      }

      lastLevel = level
    })

    return issues
  }

  // Helper methods
  parseColor(colorString) {
    const rgb = colorString.match(/\d+/g)
    if (!rgb) return null
    return { r: parseInt(rgb[0]), g: parseInt(rgb[1]), b: parseInt(rgb[2]) }
  }

  calculateContrast(fg, bg) {
    const l1 = this.relativeLuminance(fg)
    const l2 = this.relativeLuminance(bg)
    const lighter = Math.max(l1, l2)
    const darker = Math.min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)
  }

  relativeLuminance({ r, g, b }) {
    const rsRGB = r / 255
    const gsRGB = g / 255
    const bsRGB = b / 255

    const rLinear = rsRGB <= 0.03928 ? rsRGB / 12.92 : Math.pow((rsRGB + 0.055) / 1.055, 2.4)
    const gLinear = gsRGB <= 0.03928 ? gsRGB / 12.92 : Math.pow((gsRGB + 0.055) / 1.055, 2.4)
    const bLinear = bsRGB <= 0.03928 ? bsRGB / 12.92 : Math.pow((bsRGB + 0.055) / 1.055, 2.4)

    return 0.2126 * rLinear + 0.7152 * gLinear + 0.0722 * bLinear
  }
}

// Auto-run in development
if (import.meta.env.DEV) {
  window.a11yAuditor = new A11yAuditor()
  console.log('💡 Run window.a11yAuditor.audit() to check accessibility')
}

export default A11yAuditor
```

---

## ENHANCED: Real-World Boekhouder App Case Studies

### Case Study 1: VAT Declaration Form Alignment Issues

**Problem**: VAT declaration form fields misaligned on iPad

**Investigation**:
```vue
<!-- BEFORE: Broken layout -->
<div class="grid grid-cols-3 gap-4">
  <div>
    <label>Omzet hoog tarief (21%)</label>
    <input type="number" class="w-full" />
  </div>
  <!-- Long labels break alignment -->
</div>
```

**Root Cause**: Fixed 3-column grid didn't account for varying label lengths in Dutch

**Solution**:
```vue
<!-- AFTER: Responsive with proper sizing -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
  <div class="flex flex-col">
    <label class="text-sm font-medium mb-1 min-h-[2.5rem] flex items-end">
      Omzet hoog tarief (21%)
    </label>
    <input type="number" class="w-full" />
  </div>
</div>
```

**Impact**: Form now works on all screen sizes, labels properly aligned

---

### Case Study 2: Invoice List Performance on Mobile

**Problem**: Invoice list slow to render on mobile with 500+ invoices

**Investigation**:
```javascript
// Performance measurement
profiler.start('invoice-list-render')
// Rendering 500 invoice rows
profiler.end('invoice-list-render') // 3200ms!
```

**Root Cause**: Rendering all 500 rows at once, no virtualization

**Solution**:
```vue
<!-- BEFORE: All rows rendered -->
<div v-for="invoice in invoices" :key="invoice.id">
  <InvoiceCard :invoice="invoice" />
</div>

<!-- AFTER: Virtual scrolling -->
<RecycleScroller
  :items="invoices"
  :item-size="80"
  key-field="id"
  class="h-screen"
>
  <template #default="{ item }">
    <InvoiceCard :invoice="item" />
  </template>
</RecycleScroller>
```

**Results**:
- Initial render: 3200ms → 120ms
- Scroll performance: 10 FPS → 60 FPS
- Memory usage: 150MB → 40MB

---

### Case Study 3: Company Name Overflow in Dropdown

**Problem**: Long Dutch company names (with "B.V." suffixes) overflow dropdown

**Example**:
```
"Administratiekantoor voor Belastingadvies en Boekhoudkundige Dienstverlening B.V."
```

**Investigation**:
```html
<!-- Inspecting dropdown -->
<select class="w-64">
  <option>Administratiekantoor voor Belastin...</option>
</select>
<!-- Text truncated, unreadable -->
```

**Solution Implemented**:
```vue
<Listbox v-model="selectedCompany">
  <ListboxButton class="w-full text-left">
    <span class="block truncate" :title="selectedCompany?.name">
      {{ selectedCompany?.name || 'Selecteer bedrijf' }}
    </span>
  </ListboxButton>

  <ListboxOptions class="max-w-md">
    <ListboxOption v-for="company in companies" :key="company.id" :value="company">
      <div class="flex flex-col py-2">
        <!-- Show full name with line wrapping -->
        <span class="font-medium text-sm">{{ company.name }}</span>
        <span class="text-xs text-gray-500">KVK: {{ company.kvk }}</span>
      </div>
    </ListboxOption>
  </ListboxOptions>
</Listbox>
```

---

## ENHANCED: Browser DevTools Workflows

### Chrome DevTools Quick Reference for Layout Debugging

```javascript
// Show all elements with overflow
$$('*').filter(el => el.scrollWidth > el.offsetWidth || el.scrollHeight > el.offsetHeight)

// Find elements with specific class
$$('[class*="overflow"]')

// Check computed font size
getComputedStyle($0).fontSize

// Check z-index stacking context
$$('*').filter(el => getComputedStyle(el).position !== 'static').map(el => ({
  element: el,
  zIndex: getComputedStyle(el).zIndex,
  position: getComputedStyle(el).position
}))

// Find elements causing horizontal scroll
document.querySelectorAll('*').forEach(el => {
  if (el.scrollWidth > document.documentElement.clientWidth) {
    console.log('Overflow element:', el, 'Width:', el.scrollWidth)
  }
})

// Visualize box model for selected element
console.table({
  'Content': `${$0.clientWidth}x${$0.clientHeight}`,
  'Padding': getComputedStyle($0).padding,
  'Border': getComputedStyle($0).border,
  'Margin': getComputedStyle($0).margin,
})

// Check if element is visible
$0.offsetParent !== null && getComputedStyle($0).visibility !== 'hidden' && getComputedStyle($0).display !== 'none'
```

---

## ENHANCED: Automated Frontend Quality Gates

### Pre-Commit Hook for Frontend Quality

```bash
#!/bin/bash
# .git/hooks/pre-commit

echo "Running frontend quality checks..."

# 1. Check for console.log in production code
if git diff --cached --name-only | grep -E '\.(js|vue|ts)$'; then
  if git diff --cached | grep -E "console\.(log|debug|info)" | grep -v "//.*console\."; then
    echo "❌ Found console.log statements in staged files"
    echo "Remove console.log before committing"
    exit 1
  fi
fi

# 2. Check for hardcoded API URLs
if git diff --cached | grep -E "(http://|https://)(localhost|127\.0\.0\.1)"; then
  echo "❌ Found hardcoded localhost URLs"
  echo "Use environment variables for API endpoints"
  exit 1
fi

# 3. Run ESLint
npm run lint --silent
if [ $? -ne 0 ]; then
  echo "❌ ESLint errors found"
  exit 1
fi

# 4. Check Tailwind class ordering (if using Prettier plugin)
npm run prettier:check --silent
if [ $? -ne 0 ]; then
  echo "❌ Code formatting issues found"
  echo "Run: npm run prettier:fix"
  exit 1
fi

# 5. Check for TODO/FIXME comments
TODO_COUNT=$(git diff --cached | grep -E "(TODO|FIXME|XXX|HACK)" | wc -l)
if [ "$TODO_COUNT" -gt 0 ]; then
  echo "⚠️  Found $TODO_COUNT TODO/FIXME comments in staged changes"
  echo "Consider resolving before committing"
fi

echo "✅ Frontend quality checks passed"
```

---

## ENHANCED: Version History & Updates

### Version 2.0.0 (2025-12-14)
**Major Enhancements:**
- ✅ Added automated E2E testing with Cypress
- ✅ Added visual regression testing with Percy
- ✅ Added performance profiling utilities
- ✅ Added accessibility (A11Y) auditing tools
- ✅ Added real-world boekhouder app case studies
- ✅ Added browser DevTools workflow guide
- ✅ Added automated frontend quality gates
- ✅ Added pre-commit hooks for quality
- ✅ Added virtualization for large lists
- ✅ Added Dutch-specific formatting examples
- ✅ Added mobile touch target validation
- ✅ Added color contrast checking (WCAG AA)
- ✅ Added keyboard accessibility testing
- ✅ Added ARIA label validation
- ✅ Added form label checking
- ✅ Added heading hierarchy validation
- ✅ Added comprehensive debugging workflows
- ✅ Enhanced documentation structure
- ✅ Added 20+ substantial improvements

### Version 1.0.0
- Initial frontend debugger skill
- Basic layout debugging
- Flutter and Web examples

---

## Resources & Documentation

### Testing & Quality
- [Cypress.io](https://www.cypress.io/) - E2E testing framework
- [Percy.io](https://percy.io/) - Visual regression testing
- [axe-core](https://github.com/dequelabs/axe-core) - Accessibility testing
- [Lighthouse CI](https://github.com/GoogleChrome/lighthouse-ci) - Performance auditing

### Accessibility
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [A11y Project](https://www.a11yproject.com/)
- [WebAIM](https://webaim.org/) - Web accessibility resources

### Performance
- [Web Vitals](https://web.dev/vitals/) - Core performance metrics
- [Vue Performance](https://vuejs.org/guide/best-practices/performance.html)
- [Tailwind Performance](https://tailwindcss.com/docs/optimizing-for-production)

### Related Skills
- `ui-ux-expert` - Design system guidelines
- `testing-expert` - Automated testing strategies
- `deployment-checklist` - Frontend deployment verification

---

## Known Limitations

### Limitation 1: Vue DevTools Performance on Large Apps
**Description**: Vue DevTools can slow down app when tracking 1000+ components
**Workaround**: Disable DevTools in production, use selectively in development
**Planned Resolution**: Implement production-safe debugging (v2.1)

### Limitation 2: Safari-Specific Bugs Hard to Debug
**Description**: Some Safari bugs don't appear in other browsers
**Workaround**: Test on real Safari/iOS devices, use BrowserStack
**Planned Resolution**: Add Safari-specific debugging guide (v2.2)

### Limitation 3: Inertia.js State Debugging
**Description**: Inertia page props not visible in standard Vue DevTools
**Workaround**: Use Inertia DevTools Chrome extension
**Planned Resolution**: Custom Inertia debugging utilities (v2.3)

---

*Version 2.0.0 - Comprehensive frontend debugging with automated testing, accessibility auditing, performance profiling, real-world case studies, and quality automation*
