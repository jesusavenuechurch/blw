/**
 * Shrinks a picked photo in the browser before upload: fixes phone
 * rotation, caps the longest side and re-encodes as JPEG. Big phone
 * photos (and iPhone HEIC files, which Safari can decode) become ~200 KB.
 */
export async function shrinkImage(file: File, maxSide = 1200, quality = 0.86): Promise<File> {
    if (!file.type.startsWith('image/')) return file;

    let bitmap: ImageBitmap;
    try {
        bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    } catch {
        return file; // Let the server decide.
    }

    const scale = Math.min(1, maxSide / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    const ctx = canvas.getContext('2d');
    if (!ctx) return file;
    ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close?.();

    const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
    if (!blob) return file;

    return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });
}
