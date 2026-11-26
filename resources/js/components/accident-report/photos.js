// Photos are kept as data URLs (shown in the report preview), shrunk in the browser before upload.

const MAX_SIDE = 1800;
const QUALITY = 0.82;

const readAsDataUrl = (file) => new Promise((resolve, reject) => {
  const reader = new FileReader();
  reader.onload = () => resolve(reader.result);
  reader.onerror = reject;
  reader.readAsDataURL(file);
});

/** @returns {Promise<string>} JPEG data URL, max. 1800 px (original if the browser cannot decode it) */
export async function readPhoto(file) {
  const dataUrl = await readAsDataUrl(file);

  return new Promise((resolve) => {
    const image = new Image();
    image.onload = () => {
      const { naturalWidth: w, naturalHeight: h } = image;
      const factor = Math.min(1, MAX_SIDE / Math.max(w, h));
      if (!w || !h || (factor === 1 && dataUrl.length < 1.5e6 && /^data:image\/jpeg/.test(dataUrl))) {
        resolve(dataUrl);
        return;
      }
      const canvas = document.createElement('canvas');
      canvas.width = Math.round(w * factor);
      canvas.height = Math.round(h * factor);
      const context = canvas.getContext('2d');
      context.fillStyle = '#fff';
      context.fillRect(0, 0, canvas.width, canvas.height);
      context.drawImage(image, 0, 0, canvas.width, canvas.height);
      try {
        resolve(canvas.toDataURL('image/jpeg', QUALITY));
      } catch {
        resolve(dataUrl);
      }
    };
    image.onerror = () => resolve(dataUrl);
    image.src = dataUrl;
  });
}

export function dataUrlToBlob(dataUrl) {
  const [header, base64] = dataUrl.split(',');
  const mime = header.match(/:(.*?);/)?.[1] || 'application/octet-stream';
  const binary = atob(base64);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
  return new Blob([bytes], { type: mime });
}
