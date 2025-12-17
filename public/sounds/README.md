# Audio Files for Kitchen Display

## Required Files

### new-order.mp3
**Purpose**: Sound alert played when new orders arrive in the kitchen display

**Specifications**:
- Format: MP3
- Duration: 1-3 seconds
- Volume: Medium (the browser will control final volume)
- Tone: Pleasant notification sound (bell, chime, or ding)

**Where to get free sounds**:
1. **Freesound.org**: https://freesound.org/search/?q=notification
2. **Mixkit.co**: https://mixkit.co/free-sound-effects/notification/
3. **Zapsplat**: https://www.zapsplat.com/sound-effect-category/notifications/

**Recommended searches**:
- "notification bell"
- "kitchen bell"
- "order chime"
- "ding sound"

## Installation

1. Download your preferred notification sound
2. Rename it to `new-order.mp3`
3. Place it in this directory (`/public/sounds/new-order.mp3`)
4. The Kitchen Display system will automatically use it

## Alternative: Use a Simple Beep (Temporary)

If you don't want to download a file, you can modify the Vue component to use the browser's built-in beep:

```javascript
// In Display.vue, replace playAlert() with:
const playAlert = () => {
    // Browser beep (not all browsers support this)
    const beep = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZUQ0PVKzn77BdGAg+ltryxnYpBSh+zPDZizoIHGnC7ueYUA0PWqvl8LVjHAU5j9Ty03ksBSd9y/HajD4HHm3C8OOXUg0PV6zm8LFgGQc9ltrzxnkrBCZ7y/HbjD8IHm3C8OSaVw8PWa7m8LNiFQc8l9vzwn0vBCV5yPHdjUEHIG/D8OSbVxAPV67n8bJkGwY7l9vywn0vBCV5yPDdjUEHIG/D8OSaVxAPV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHIG/D8OSaVxAPV67m8LNjGwY7l9vywn0vBSV5yPDdjUEHIG/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8PV67m8LNkGwY7l9vywn0vBSV5yPDdjUEHH2/D8OSaVw8=');
    beep.play();
};
```

## Testing

After adding the sound file:
1. Navigate to `/kitchen/display`
2. Create a new sale from the POS
3. The sound should play when the new order appears

## Troubleshooting

**Sound not playing?**
- Check browser console for errors
- Verify file path is correct: `/public/sounds/new-order.mp3`
- Some browsers require user interaction before playing audio
- Check browser's autoplay policy settings
