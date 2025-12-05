import ffmpeg from 'fluent-ffmpeg'
import ffmpegPath from 'ffmpeg-static'
import path from 'path'

ffmpeg.setFfmpegPath(ffmpegPath)

const [,, input, output] = process.argv

if (!input || !output) {
    console.error('Usage: node convert-ogg-to-mp3.mjs input.ogg output.mp3')
    process.exit(1)
}

ffmpeg(input)
    .audioCodec('libmp3lame')
    .format('mp3')
    .save(output)
    .on('end', () => console.log('Done:', output))
    .on('error', err => console.error('Error:', err))
