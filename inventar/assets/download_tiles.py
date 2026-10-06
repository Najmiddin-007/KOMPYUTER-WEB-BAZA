import os, urllib.request, time, concurrent.futures

OUT = r"C:\xampp\htdocs\inventar\assets\map_tiles"
LAT_MIN, LAT_MAX = 37.9, 39.9
LNG_MIN, LNG_MAX = 64.8, 67.3
ZOOMS = [9, 10, 11]

# Muqobil serverlar (OSM asosiy serveri bloklagan)
SOURCES = [
    "https://a.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png",
    "https://b.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png",
    "https://c.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png",
    "https://a.tile.opentopomap.org/{z}/{x}/{y}.png",
]

def lon2x(lng, z): return int((lng + 180.0) / 360.0 * (2 ** z))
def lat2y(lat, z):
    import math
    lat_r = math.radians(lat)
    return int((1.0 - math.asinh(math.tan(lat_r)) / math.pi) / 2.0 * (2 ** z))

tiles = []
for z in ZOOMS:
    x1, x2 = lon2x(LNG_MIN, z), lon2x(LNG_MAX, z)
    y1, y2 = lat2y(LAT_MAX, z), lat2y(LAT_MIN, z)
    tiles += [(z, x, y) for x in range(x1, x2 + 1) for y in range(y1, y2 + 1)]
print(f"Jami: {len(tiles)}")

def fetch(t):
    z, x, y = t
    d = os.path.join(OUT, str(z), str(x))
    f = os.path.join(d, f"{y}.png")
    os.makedirs(d, exist_ok=True)
    for src in SOURCES:
        url = src.format(z=z, x=x, y=y)
        req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0 (XAMPP local inventory map)"})
        try:
            with urllib.request.urlopen(req, timeout=30) as r:
                data = r.read()
            if len(data) < 2000 or len(data) == 6987:  # blok-rasm hajmi
                continue
            tmp = f + ".tmp"
            with open(tmp, "wb") as fp:
                fp.write(data)
            os.replace(tmp, f)
            return (t, len(data))
        except Exception:
            time.sleep(1)
    return (t, 0)

ok, sizes, fail = 0, set(), []
with concurrent.futures.ThreadPoolExecutor(max_workers=2) as ex:
    for t, n in ex.map(fetch, tiles):
        if n: ok += 1; sizes.add(n)
        else: fail.append(t)
        time.sleep(0.12)
print(f"Muvaffaqiyatli: {ok} / {len(tiles)}; xato: {len(fail)}; noyob hajmlar: {len(sizes)}")
if fail: print("Yuklanmadi:", fail)
