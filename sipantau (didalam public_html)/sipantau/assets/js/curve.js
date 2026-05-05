    // curve.js

function generateDynamicSCurve(targetTotal, startPercent, startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const days = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;

    const k = 0.25; // kemiringan kurva
    const midPoint = days / 2;

    const startFrac = startPercent / 100;
    const endFrac = 1.0;

    const f0 = 1 / (1 + Math.exp(-k * (0 - midPoint)));
    const f1 = 1 / (1 + Math.exp(-k * (days - midPoint)));

    const scaleA = (endFrac - startFrac) / (f1 - f0);
    const scaleB = startFrac - scaleA * f0;

    const curve = [];
    for (let i = 0; i < days; i++) {
        const f = 1 / (1 + Math.exp(-k * (i - midPoint)));
        const progress = scaleA * f + scaleB;
        const value = targetTotal * progress;
        curve.push(Math.round(value));
    }

    return curve;
}

function generateActualCurve(actualPerDay) {
    const cumulative = [];
    let total = 0;
    for (const val of actualPerDay) {
        total += val;
        cumulative.push(total);
    }
    return cumulative;
}
