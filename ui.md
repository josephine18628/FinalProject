Goal:
Build a modern, clean quiz-builder interface similar to the reference layout. The design should replicate the UI shown, including the sidebar, top question header, multiple-choice answer cards, and the “Add next question” action. Use Next.js + ShadCN components. All images should be real Unsplash images (not empty placeholders).

1. Website Description

Create a quiz question editor page with a two-panel layout:

A vertical left sidebar containing navigation icons and a circular user avatar at the top.

A main content card on the right that contains:

Question number and type selector (“Multiple Choice”).

A centered question text.

Four answer cards (A, B, C, D), each with:

A colored badge for the letter.

Answer text.

“Mark as correct” radio button.

Bottom actions: “Save & Exit” (left aligned) and “Add next question” (right aligned).

The overall look should be clean, minimal, and spaced generously with soft shadows.

2. Color Palette (use these exact shades)

Background Light Gray: #E6E6EF

White Card Background: #FFFFFF

Soft Red (Incorrect option background): #FFE5E5

Soft Green (Correct option background): #E4F7F0

Soft Border Gray: #E5E7EB

Black Text: #111111

Gray Text: #6B7280

Primary Blue Accent (buttons, active states): #2563EB

3. Design Principles
Layout

Use a full-width container with left sidebar ~80–100px wide.

Main content card should have:

Rounded corners: 24px

Padding: 48px

Shadow: subtle, soft, diffuse.

Spacing

Maintain generous spacing around elements.

Vertical spacing between blocks: 24px–32px.

Spacing between options: 20–24px.

Typography

Use a clean sans-serif font (e.g., Inter).
Hierarchy:

Question text: 24–28px, semibold, center aligned.

Answer text: 16px

Labels and helpers: 14px, gray.

Alignment

Question and answer area centered horizontally.

Sidebar icons vertically spaced and center-aligned.

Action buttons positioned at bottom left and bottom right.

4. UI Details to Reproduce
Sidebar

Vertical white sidebar card with rounded left edges.

Small shadow to separate from background.

Top avatar (use a dummy Unsplash portrait).

Below the avatar: a vertical stack of icons (use Lucide icons via ShadCN).

Icons evenly spaced.

Hover states with subtle blue tint.

Main Question Card

Large white rounded card.

Contains:

“Question 1” text in top-left (14px, gray).

“Add time +” button in top-right (blue icon button).

"MULTIPLE CHOICE ▼" selector centered above the question.

Main question text centered.

Answer Options

Each option is a horizontal card with:

Colored background:

Red (#FFE5E5) for incorrect options.

Green (#E4F7F0) for correct option.

A small letter badge:

Red or green solid background.

White letter inside (A, B, C, D).

Slightly rounded corners.

Answer text to the right of the badge.

A radio button below the card labeled "Mark as correct".

Bottom Actions

Save & Exit: small text link, left-aligned.

Add next question: primary blue button with arrow icon, right-aligned.

Use spacing consistent with the screenshot.

5. Interactivity

Selecting "Mark as correct" should visually change the card color to green and unselect others.

Hover states for answer cards: slightly darkened border.

Sidebar icons highlight on hover (subtle blue background).

6. Images

Use Unsplash images for:

The user avatar inside the sidebar.

Any sections that need imagery (use dummy but relevant Unsplash URLs).

7. Additional Requirements

The UI should be fully responsive.

All elements should be created using ShadCN components, extended where necessary.

Use real dummy images instead of empty placeholders.

Remember never to use emogis, use svg